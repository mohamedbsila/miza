<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\HomeContentRepository;

#[Route('/gallery')]
class GalleryController extends AbstractController
{
    #[Route('/kitchen', name: 'app_gallery_kitchen')]
    public function kitchen(HomeContentRepository $homeContentRepository): Response
    {
        $content = $this->getGalleryContent($homeContentRepository, 'kitchen');
        return $this->render('gallery/show.html.twig', [
            'gallery' => $content,
            'type' => 'kitchen'
        ]);
    }

    #[Route('/bathroom', name: 'app_gallery_bathroom')]
    public function bathroom(HomeContentRepository $homeContentRepository): Response
    {
        $content = $this->getGalleryContent($homeContentRepository, 'bathroom');
        return $this->render('gallery/show.html.twig', [
            'gallery' => $content,
            'type' => 'bathroom'
        ]);
    }

    #[Route('/entry', name: 'app_gallery_entry')]
    public function entry(HomeContentRepository $homeContentRepository): Response
    {
        $content = $this->getGalleryContent($homeContentRepository, 'entry');
        return $this->render('gallery/show.html.twig', [
            'gallery' => $content,
            'type' => 'entry'
        ]);
    }

    #[Route('/dining', name: 'app_gallery_dining')]
    public function dining(HomeContentRepository $homeContentRepository): Response
    {
        $content = $this->getGalleryContent($homeContentRepository, 'dining');
        return $this->render('gallery/show.html.twig', [
            'gallery' => $content,
            'type' => 'dining'
        ]);
    }

    #[Route('/living', name: 'app_gallery_living')]
    public function living(HomeContentRepository $homeContentRepository): Response
    {
        $content = $this->getGalleryContent($homeContentRepository, 'living');
        return $this->render('gallery/show.html.twig', [
            'gallery' => $content,
            'type' => 'living'
        ]);
    }

    #[Route('/bedroom', name: 'app_gallery_bedroom')]
    public function bedroom(HomeContentRepository $homeContentRepository): Response
    {
        $content = $this->getGalleryContent($homeContentRepository, 'bedroom');
        return $this->render('gallery/show.html.twig', [
            'gallery' => $content,
            'type' => 'bedroom'
        ]);
    }

    private function getGalleryContent(HomeContentRepository $repository, string $type): array
    {
        $galleriesContent = $repository->findBySection('galleries');
        $galleryData = $galleriesContent ? json_decode($galleriesContent->getDescription(), true) ?: [] : [];
        
        return [
            'title' => $galleryData[$type]['title'] ?? ucfirst($type) . ' Gallery',
            'buttonText' => $galleryData[$type]['buttonText'] ?? 'VIEW MORE',
            'image' => $galleriesContent?->getImage() ?: "/images/galleries/{$type}.jpg"
        ];
    }
} 