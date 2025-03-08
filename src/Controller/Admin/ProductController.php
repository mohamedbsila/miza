<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\ProductImage;
use App\Form\ProductType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

#[Route('/admin/product')]
class ProductController extends AbstractController
{
    private $formFactory;
    private $entityManager;

    public function __construct(FormFactoryInterface $formFactory, EntityManagerInterface $entityManager)
    {
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'admin_product_index', methods: ['GET'])]
    public function index(): Response
    {
        $products = $this->entityManager->getRepository(Product::class)->findAll();

        return $this->render('admin/product/index.html.twig', [
            'products' => $products,
        ]);
    }

    #[Route('/new', name: 'admin_product_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $product = new Product();
        $form = $this->formFactory->create(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $uploadDir = $this->getParameter('product_images_directory');
                
                // Create upload directory if it doesn't exist
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

            $imageOnFiles = $form->get('imageOnFiles')->getData();
            $imageOffFiles = $form->get('imageOffFiles')->getData();
            
            if ($imageOnFiles && $imageOffFiles && count($imageOnFiles) === count($imageOffFiles)) {
                foreach ($imageOnFiles as $index => $imageOnFile) {
                        if (!$imageOnFile->isValid()) {
                            throw new \Exception('Invalid file upload for ON state image');
                        }

                    $productImage = new ProductImage();
                    
                    // Handle ON state image
                        $fileNameOn = md5(uniqid()) . '_' . $imageOnFile->getClientOriginalName();
                        try {
                    $imageOnFile->move(
                                $uploadDir,
                        $fileNameOn
                    );
                        } catch (\Exception $e) {
                            throw new \Exception('Error uploading ON state image: ' . $e->getMessage());
                        }
                    $productImage->setImageOn($fileNameOn);
                    
                    // Handle OFF state image
                    $imageOffFile = $imageOffFiles[$index];
                        if (!$imageOffFile->isValid()) {
                            throw new \Exception('Invalid file upload for OFF state image');
                        }

                        $fileNameOff = md5(uniqid()) . '_' . $imageOffFile->getClientOriginalName();
                        try {
                    $imageOffFile->move(
                                $uploadDir,
                        $fileNameOff
                    );
                        } catch (\Exception $e) {
                            throw new \Exception('Error uploading OFF state image: ' . $e->getMessage());
                        }
                    $productImage->setImageOff($fileNameOff);
                    
                    // Set other properties
                    $productImage->setIsPrimary($index === 0);
                    $productImage->setSortOrder($index);
                    $productImage->setProduct($product);
                    $product->addImage($productImage);

                    // Set the first image as the product's main image
                    if ($index === 0) {
                        $product->setImage($fileNameOn);
                    }
                }
            }

            // Handle color variants
                if ($product->getColors()) {
                    // Check if number of colors exceeds maximum
                    if (count($product->getColors()) > 4) {
                        throw new \Exception('Maximum number of colors (4) exceeded. Please remove some colors.');
                    }

                    $colorIndex = 0; // Initialize counter for colors
            foreach ($product->getColors() as $color) {
                        // Set the correct sort order
                        $color->setSortOrder($colorIndex);
                        
                        // Debug information
                        $this->addFlash('info', sprintf('Processing color %s at index %d of 4', $color->getColorName(), $colorIndex + 1));
                        
                        try {
                            // Get the color form data safely
                            $colorForm = null;
                            $colors = $form->get('colors');
                            foreach ($colors as $key => $formColor) {
                                if ((int)$key === $colorIndex) {
                                    $colorForm = $formColor;
                                    break;
                                }
                            }
                            
                            if (!$colorForm) {
                                throw new \Exception(sprintf('Form data not found for color %s at index %d', $color->getColorName(), $colorIndex));
                            }

                            // Get the file data
                            $imageOnFile = $colorForm->get('imageOnFile')->getData();
                            $imageOffFile = $colorForm->get('imageOffFile')->getData();

                            if (!$imageOnFile || !$imageOffFile) {
                                throw new \Exception(sprintf('Missing image files for color %s', $color->getColorName()));
                            }

                            // Create upload directory if it doesn't exist
                            if (!file_exists($uploadDir)) {
                                mkdir($uploadDir, 0777, true);
                            }

                            // Process ON state image
                            $fileNameOn = sprintf('%s_%s_%d_%s', uniqid(), $color->getColorName(), $colorIndex, $imageOnFile->getClientOriginalName());
                            $fileNameOn = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileNameOn);
                            
                            // Process OFF state image
                            $fileNameOff = sprintf('%s_%s_%d_%s', uniqid(), $color->getColorName(), $colorIndex, $imageOffFile->getClientOriginalName());
                            $fileNameOff = preg_replace('/[^a-zA-Z0-9._-]/', '_', $fileNameOff);

                            // Move the files
                            $imageOnFile->move($uploadDir, $fileNameOn);
                            $imageOffFile->move($uploadDir, $fileNameOff);

                            // Set the image paths
                            $color->setImageOn($fileNameOn);
                            $color->setImageOff($fileNameOff);
                            $color->setProduct($product);

                            // Debug information
                            $this->addFlash('info', sprintf('Successfully processed color %s (%d of 4)', $color->getColorName(), $colorIndex + 1));

                        } catch (\Exception $e) {
                            // Clean up any uploaded files
                            if (isset($fileNameOn) && file_exists($uploadDir . '/' . $fileNameOn)) {
                                unlink($uploadDir . '/' . $fileNameOn);
                            }
                            if (isset($fileNameOff) && file_exists($uploadDir . '/' . $fileNameOff)) {
                                unlink($uploadDir . '/' . $fileNameOff);
                            }

                            throw new \Exception(sprintf(
                                'Error processing color %s (%d of 4): %s',
                                $color->getColorName(),
                                $colorIndex + 1,
                                $e->getMessage()
                            ));
                        }

                        $colorIndex++; // Increment the counter for the next color
                    }

                    // Add information about remaining color slots
                    $remainingSlots = 4 - count($product->getColors());
                    if ($remainingSlots > 0) {
                        $this->addFlash('info', sprintf('You can add %d more color(s) to this product', $remainingSlots));
                }
            }

            $this->entityManager->persist($product);
            $this->entityManager->flush();

            $this->addFlash('success', 'Product created successfully');
            return $this->redirectToRoute('admin_product_index');
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error creating product: ' . $e->getMessage());
            }
        }

        return $this->render('admin/product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/delete', name: 'admin_product_delete', methods: ['POST', 'DELETE'])]
    public function delete(Request $request, Product $product): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            // Delete associated images from filesystem
            foreach ($product->getImages() as $image) {
                $imageOnPath = $this->getParameter('product_images_directory').'/'.$image->getImageOn();
                $imageOffPath = $this->getParameter('product_images_directory').'/'.$image->getImageOff();
                
                if (file_exists($imageOnPath)) {
                    unlink($imageOnPath);
                }
                if (file_exists($imageOffPath)) {
                    unlink($imageOffPath);
                }
            }

            $this->entityManager->remove($product);
            $this->entityManager->flush();

            $this->addFlash('success', 'Product deleted successfully');

            if ($request->isXmlHttpRequest()) {
                return new JsonResponse(['success' => true]);
            }
        }

        return $this->redirectToRoute('admin_product_index');
    }

    #[Route('/{id}/update', name: 'admin_product_update', methods: ['POST'])]
    public function update(Request $request, Product $product): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $field = $data['field'];
        $value = $data['value'];

        // Validate field name to prevent arbitrary property setting
        $allowedFields = ['name', 'sku', 'price', 'stockQuantity'];
        if (!in_array($field, $allowedFields)) {
            return new JsonResponse(['success' => false, 'message' => 'Invalid field'], 400);
        }

        // Use setter method based on field name
        $setter = 'set' . ucfirst($field);
        if (method_exists($product, $setter)) {
            // Convert value type based on field
            switch ($field) {
                case 'price':
                    $value = (float) str_replace('$', '', $value);
                    break;
                case 'stockQuantity':
                    $value = (int) $value;
                    break;
            }

            $product->$setter($value);
            $this->entityManager->flush();

            return new JsonResponse(['success' => true]);
        }

        return new JsonResponse(['success' => false, 'message' => 'Invalid field'], 400);
    }
} 