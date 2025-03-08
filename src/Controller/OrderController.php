<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Doctrine\ORM\EntityManagerInterface;

#[IsGranted('ROLE_USER')]
class OrderController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/orders', name: 'app_orders')]
    public function index(): Response
    {
        $user = $this->getUser();
        $orders = $this->entityManager->getRepository('App:Order')
            ->findBy(['user' => $user], ['createdAt' => 'DESC']);

        return $this->render('order/index.html.twig', [
            'orders' => $orders
        ]);
    }

    #[Route('/orders/{id}', name: 'app_order_detail')]
    public function detail(int $id): Response
    {
        $order = $this->entityManager->getRepository('App:Order')->find($id);

        if (!$order || $order->getUser() !== $this->getUser()) {
            throw $this->createNotFoundException('Order not found');
        }

        return $this->render('order/detail.html.twig', [
            'order' => $order
        ]);
    }
} 