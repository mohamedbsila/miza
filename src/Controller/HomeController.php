<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\HomeContentRepository;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\GalleryRepository;
use App\Repository\SpotlightArticleRepository;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        HomeContentRepository $homeContentRepository,
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        GalleryRepository $galleryRepository,
        SpotlightArticleRepository $spotlightArticleRepository
    ): Response {
        // Initialize content array
        $content = [];

        // Get content for each section (excluding galleries and spotlight)
        foreach (['hero', 'icons', 'cordless', 'shop', 'explore'] as $section) {
            $sectionContent = $homeContentRepository->findBySection($section);
            if ($sectionContent) {
                $content[$section] = [
                    'title' => $sectionContent->getTitle(),
                    'description' => $sectionContent->getDescription(),
                    'buttonText' => $sectionContent->getButtonText(),
                    'image' => $sectionContent->getImage()
                ];
            }
        }

        // Fetch spotlight articles
        $spotlightArticles = $spotlightArticleRepository->findBy([], ['publishDate' => 'DESC'], 3); // Limit to 3
        $content['spotlight'] = [
            'title' => $homeContentRepository->findBySection('spotlight')?->getTitle() ?? 'The Spotlight',
            'description' => $homeContentRepository->findBySection('spotlight')?->getDescription() ?? '...',
            'buttonText' => $homeContentRepository->findBySection('spotlight')?->getButtonText() ?? 'VIEW MORE',
            'image' => $homeContentRepository->findBySection('spotlight')?->getImage() ?? 'uploads/home/spotlight-section.jpg',
            'items' => $spotlightArticles
        ];

        // Fetch galleries from Gallery entity
        $galleries = $galleryRepository->findAll();
        $galleryData = [];
        foreach ($galleries as $gallery) {
            $galleryData[$gallery->getRoomType()] = [
                'title' => $gallery->getName(),
                'buttonText' => $gallery->getDescription() ?? 'VIEW MORE',
                'image' => $gallery->getImageUrl()
            ];
        }

        // Define default galleries if not all are present
        $defaultGalleries = [
            'kitchen' => ['title' => 'Kitchen Gallery', 'buttonText' => 'VIEW MORE', 'image' => '/images/galleries/kitchen.jpg'],
            'bathroom' => ['title' => 'Bathroom Gallery', 'buttonText' => 'VIEW MORE', 'image' => '/images/galleries/bathroom.jpg'],
            'entry' => ['title' => 'Entry & Hall Gallery', 'buttonText' => 'VIEW MORE', 'image' => '/images/galleries/entry.jpg'],
            'dining' => ['title' => 'Dining Room Gallery', 'buttonText' => 'VIEW MORE', 'image' => '/images/galleries/dining.jpg'],
            'living' => ['title' => 'Living Room Gallery', 'buttonText' => 'VIEW MORE', 'image' => '/images/galleries/living.jpg'],
            'bedroom' => ['title' => 'Bedroom Gallery', 'buttonText' => 'VIEW MORE', 'image' => '/images/galleries/bedroom.jpg']
        ];

        // Merge with defaults
        $content['galleries'] = [
            'title' => $homeContentRepository->findBySection('galleries')?->getTitle() ?? 'Inspiration Galleries',
            'galleries' => array_merge($defaultGalleries, $galleryData)
        ];

        // Get explore products
        $exploreProducts = $productRepository->findBy(['isExploreProduct' => true], ['id' => 'DESC'], 6);

        // Get all categories for the menu
        $categories = $categoryRepository->findAll();

        return $this->render('home/index.html.twig', [
            'content' => $content,
            'exploreProducts' => $exploreProducts,
            'categories' => $categories
        ]);
    }
}