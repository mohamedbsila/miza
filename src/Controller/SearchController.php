<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SearchController extends AbstractController
{
    #[Route('/api/search', name: 'api_search', methods: ['GET'])]
    public function search(Request $request, ProductRepository $productRepository): JsonResponse
    {
        $query = $request->query->get('q');
        
        if (empty($query) || strlen($query) < 2) {
            return new JsonResponse([
                'results' => [],
                'suggestions' => []
            ]);
        }

        $products = $productRepository->search($query);
        $suggestions = [];

        // If no exact matches, get suggestions
        if (count($products) === 0) {
            $suggestions = $productRepository->findSimilarProducts($query);
        }

        $results = array_map(function($product) {
            return [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPrice(),
                'image' => '/uploads/products/' . $product->getImage(),
                'category' => $product->getCategory() ? $product->getCategory()->getName() : null,
            ];
        }, $products);

        return new JsonResponse([
            'results' => $results,
            'suggestions' => $suggestions
        ]);
    }
} 
 