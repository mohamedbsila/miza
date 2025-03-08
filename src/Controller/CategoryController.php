<?php

namespace App\Controller;

use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/category')]
class CategoryController extends AbstractController
{
    #[Route('/{id}', name: 'category_view', methods: ['GET'])]
    public function view(Category $category): Response
    {
        return $this->render('category/view.html.twig', [
            'category' => $category,
            'products' => $category->getProducts(),
        ]);
    }
} 