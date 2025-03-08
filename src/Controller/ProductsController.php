<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;

#[Route('/products')]
class ProductsController extends AbstractController
{
    private $entityManager;
    private $productRepository;
    private $categoryRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository
    ) {
        $this->entityManager = $entityManager;
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
    }

    #[Route('/', name: 'app_products')]
    public function index(Request $request): Response
    {
        // Get filter parameters
        $filters = [
            'category' => $request->query->get('category'),
            'finish' => $request->query->get('finish'),
            'style' => $request->query->get('style'),
            'height' => $request->query->get('height'),
            'width' => $request->query->get('width'),
            'designer' => $request->query->get('designer'),
            'collection' => $request->query->get('collection'),
            'lamping' => $request->query->get('lamping'),
            'color_temperature' => $request->query->get('color_temperature'),
            'dimming' => $request->query->get('dimming'),
            'input_voltage' => $request->query->get('input_voltage'),
            'price_range' => $request->query->get('price_range'),
        ];

        $sort = $request->query->get('sort', 'featured');
        $query = $request->query->get('q');

        // Get filter options for dropdowns
        $categories = $this->categoryRepository->findAll();
        $finishes = $this->productRepository->getUniqueValues('finish');
        $styles = $this->productRepository->getUniqueValues('style');
        $heights = $this->productRepository->getUniqueValues('height');
        $widths = $this->productRepository->getUniqueValues('width');
        $designers = $this->productRepository->getUniqueValues('designer');
        $collections = $this->productRepository->getUniqueValues('collection');
        $lampings = $this->productRepository->getUniqueValues('lamping');
        $colorTemperatures = $this->productRepository->getUniqueValues('colorTemperature');
        $dimmings = $this->productRepository->getUniqueValues('dimming');
        $inputVoltages = $this->productRepository->getUniqueValues('inputVoltage');

        // Get filtered and sorted products
        $products = $this->productRepository->findByFilters($filters, $sort, $query);

        return $this->render('products/index.html.twig', [
            'products' => $products,
            'categories' => $categories,
            'finishes' => $finishes,
            'styles' => $styles,
            'heights' => $heights,
            'widths' => $widths,
            'designers' => $designers,
            'collections' => $collections,
            'lampings' => $lampings,
            'colorTemperatures' => $colorTemperatures,
            'dimmings' => $dimmings,
            'inputVoltages' => $inputVoltages,
            'search_query' => $query
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        $categories = $this->categoryRepository->findAll();
        return $this->render('products/show.html.twig', [
            'product' => $product,
            'categories' => $categories
        ]);
    }
} 