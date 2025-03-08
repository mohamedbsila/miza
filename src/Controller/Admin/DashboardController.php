<?php

namespace App\Controller\Admin;

use App\Entity\Product;
use App\Entity\Category;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class DashboardController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'admin_dashboard')]
    public function index(): Response
    {
        $productRepository = $this->entityManager->getRepository(Product::class);
        $categoryRepository = $this->entityManager->getRepository(Category::class);

        return $this->render('admin/dashboard/index.html.twig', [
            'products_count' => $productRepository->count([]),
            'categories_count' => $categoryRepository->count([]),
            'featured_count' => $productRepository->count(['isFeatured' => true]),
            'recent_products' => $productRepository->findBy([], ['createdAt' => 'DESC'], 5),
            'recent_categories' => $categoryRepository->findBy([], ['id' => 'DESC'], 5),
        ]);
    }
} 