<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\Order;
use App\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use App\Repository\ProductRepository;
use App\Repository\OrderRepository;

#[Route('/cart', name: 'app_cart_')]
class CartController extends AbstractController
{
    #[Route('/paypal-setup', name: 'paypal_setup')]
    public function paypalSetup(): Response
    {
        return $this->render('cart/paypal_setup_instructions.html.twig', [
            'paypal_client_id' => $this->getParameter('paypal_client_id')
        ]);
    }

    #[Route('/test-payment', name: 'test_payment')]
    public function testPayment(): Response
    {
        // Create a dummy cart item for testing
        $testProduct = new \stdClass();
        $testProduct->id = 999;
        $testProduct->name = "Test Product";
        $testProduct->price = 25.99;
        $testProduct->images = [new \stdClass()];
        $testProduct->images[0]->imageOn = "placeholder.jpg";
        
        $cartItems = [];
        $cartItems[] = [
            'product' => $testProduct,
            'quantity' => 1,
            'colorCode' => null
        ];
        
        return $this->render('cart/index.html.twig', [
            'cart' => $cartItems,
            'paypal_client_id' => $this->getParameter('paypal_client_id'),
            'testing_mode' => true
        ]);
    }

    #[Route('/', name: 'index')]
    public function index(SessionInterface $session, ProductRepository $productRepository): Response
    {
        $cart = $session->get('cart', []);
        $cartItems = [];
        
        foreach ($cart as $key => $item) {
            $product = $productRepository->find($item['id']);
            if ($product) {
                $cartItems[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'colorCode' => $item['colorId'] ?? null
                ];
            }
        }
        
        return $this->render('cart/index.html.twig', [
            'cart' => $cartItems,
            'paypal_client_id' => $this->getParameter('paypal_client_id')
        ]);
    }

    #[Route('/order-confirmation/{id}', name: 'order_confirmation')]
    public function orderConfirmation(Order $order): Response
    {
        // Ensure the order belongs to the current user
        if ($order->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You cannot view this order.');
        }

        return $this->render('cart/order_confirmation.html.twig', [
            'order' => $order
        ]);
    }

    #[Route('/process-payment', name: 'process_payment', methods: ['POST'])]
    public function processPayment(Request $request, SessionInterface $session, EntityManagerInterface $entityManager): JsonResponse
    {
        if (!$this->isCsrfTokenValid('payment', $request->headers->get('X-CSRF-TOKEN'))) {
            return $this->json(['success' => false, 'message' => 'Invalid CSRF token'], 403);
        }

        $data = json_decode($request->getContent(), true);
        $cart = $session->get('cart', []);
        
        // Check if this is a test payment
        $isTestPayment = false;
        if (isset($data['testPayment']) && $data['testPayment'] === true) {
            $isTestPayment = true;
        } else if (empty($cart)) {
            return $this->json(['success' => false, 'message' => 'Cart is empty'], 400);
        }

        try {
            // Create new order
            $order = new Order();
            $order->setUser($this->getUser() ?: null); // Allow anonymous orders for testing
            $order->setStatus('paid');
            $order->setPaymentId($data['orderId'] ?? 'test-'.uniqid());
            $order->setCreatedAt(new \DateTime());
            
            $total = 0;
            
            // Add order items
            if ($isTestPayment) {
                // Create a test order item
                $orderItem = new OrderItem();
                $orderItem->setOrder($order);
                // Try to find a real product for the test order
                $product = $entityManager->getRepository(Product::class)->findOneBy([]);
                if (!$product) {
                    // Create a placeholder product record if needed
                    $product = new Product();
                    $product->setName('Test Product');
                    $product->setPrice(25.99);
                    $product->setDescription('Test product for PayPal integration testing');
                    $product->setStockQuantity(999);
                    $entityManager->persist($product);
                }
                
                $orderItem->setProduct($product);
                $orderItem->setQuantity(1);
                $orderItem->setPrice(25.99);
                
                $total = 25.99;
                
                $entityManager->persist($orderItem);
            } else {
                // Process normal cart items
                foreach ($cart as $key => $item) {
                    $product = $entityManager->getRepository(Product::class)->find($item['id']);
                    if (!$product) {
                        continue;
                    }

                    $orderItem = new OrderItem();
                    $orderItem->setOrder($order);
                    $orderItem->setProduct($product);
                    $orderItem->setQuantity($item['quantity']);
                    $orderItem->setPrice($product->getPrice());
                    $orderItem->setColorCode($item['colorId'] ?? null);
                    
                    $total += $product->getPrice() * $item['quantity'];
                    
                    $entityManager->persist($orderItem);
                    
                    // Update product stock
                    $newStock = $product->getStockQuantity() - $item['quantity'];
                    $product->setStockQuantity($newStock);
                }
            }
            
            $order->setTotal($total);
            $entityManager->persist($order);
            $entityManager->flush();
            
            // Clear the cart if not a test payment
            if (!$isTestPayment) {
                $session->remove('cart');
            }
            
            return $this->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'orderId' => $order->getId(),
                'redirectUrl' => $this->generateUrl('app_cart_order_confirmation', ['id' => $order->getId()])
            ]);
            
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error processing payment: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/add/{id}', name: 'add', methods: ['POST'])]
    public function add(Request $request, Product $product, SessionInterface $session): JsonResponse
    {
        if (!$this->isCsrfTokenValid('cart_add', $request->headers->get('X-CSRF-TOKEN'))) {
            return $this->json(['success' => false, 'message' => 'Invalid CSRF token'], 403);
        }

        $data = json_decode($request->getContent(), true);
        $quantity = (int)($data['quantity'] ?? 1);
        $colorId = $data['colorId'] ?? null;

        // Validate quantity
        if ($quantity <= 0) {
            return $this->json(['success' => false, 'message' => 'Invalid quantity'], 400);
        }

        // Check stock
        if ($product->getStockQuantity() < $quantity) {
            return $this->json(['success' => false, 'message' => 'Not enough stock available'], 400);
        }

        // Get current cart
        $cart = $session->get('cart', []);
        
        // Create unique key for product+color combination
        $cartKey = $colorId ? $product->getId() . '-' . $colorId : $product->getId();
        
        // Add or update cart item
        if (isset($cart[$cartKey])) {
            $cart[$cartKey]['quantity'] += $quantity;
        } else {
            $cart[$cartKey] = [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'quantity' => $quantity,
                'colorId' => $colorId
            ];
        }

        // Save cart back to session
        $session->set('cart', $cart);

        return $this->json([
            'success' => true,
            'message' => 'Product added to cart successfully',
            'cartCount' => array_sum(array_column($cart, 'quantity'))
        ]);
    }

    #[Route('/remove/{id}', name: 'remove', methods: ['POST'])]
    public function remove(string $id, SessionInterface $session): JsonResponse
    {
        $cart = $session->get('cart', []);
        
        if (isset($cart[$id])) {
            unset($cart[$id]);
            $session->set('cart', $cart);
            
            return $this->json([
                'success' => true,
                'message' => 'Product removed from cart',
                'cartCount' => array_sum(array_column($cart, 'quantity'))
            ]);
        }
        
        return $this->json(['success' => false, 'message' => 'Product not found in cart'], 404);
    }

    #[Route('/update/{id}', name: 'update', methods: ['POST'])]
    public function update(string $id, Request $request, SessionInterface $session): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $quantity = (int)($data['quantity'] ?? 0);
        
        if ($quantity <= 0) {
            return $this->json(['success' => false, 'message' => 'Invalid quantity'], 400);
        }
        
        $cart = $session->get('cart', []);
        
        if (isset($cart[$id])) {
            $cart[$id]['quantity'] = $quantity;
            $session->set('cart', $cart);
            
            return $this->json([
                'success' => true,
                'message' => 'Cart updated successfully',
                'cartCount' => array_sum(array_column($cart, 'quantity'))
            ]);
        }
        
        return $this->json(['success' => false, 'message' => 'Product not found in cart'], 404);
    }
} 