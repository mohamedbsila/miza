<?php

namespace App\Controller;

use App\Entity\Gallery;
use App\Entity\HomeContent;
use App\Entity\Product;
use App\Entity\SpotlightArticle;
use App\Repository\HomeContentRepository;
use App\Repository\SpotlightArticleRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/home-editor/save', name: 'admin_home_editor_save', methods: ['POST'])]
    public function saveHomeContent(
        Request $request,
        EntityManagerInterface $entityManager,
        HomeContentRepository $homeContentRepository,
        SpotlightArticleRepository $spotlightArticleRepository
    ): Response
    {
        $data = json_decode($request->getContent(), true);

        // Save non-gallery and non-spotlight sections
        foreach (['hero', 'icons', 'cordless', 'shop', 'explore'] as $section) {
            if (isset($data[$section])) {
                $content = $homeContentRepository->findOneBy(['section' => $section]);
                if (!$content) {
                    $content = new HomeContent();
                    $content->setSection($section);
                    $entityManager->persist($content);
                }
                $content->setTitle($data[$section]['title'] ?? null);
                $content->setDescription($data[$section]['description'] ?? null);
                $content->setButtonText($data[$section]['buttonText'] ?? null);
                $content->setUpdatedAt(new \DateTimeImmutable());
            }
        }

        // Save spotlight articles
        if (isset($data['spotlight']['items'])) {
            foreach ($data['spotlight']['items'] as $articleData) {
                $article = null;
                if (isset($articleData['id'])) {
                    $article = $spotlightArticleRepository->find($articleData['id']);
                }
                if (isset($articleData['_delete']) && $articleData['_delete'] && $article) {
                    $entityManager->remove($article);
                    continue;
                }
                if (!$article) {
                    $article = new SpotlightArticle();
                    $entityManager->persist($article);
                }
                $article->setTitle($articleData['title'] ?? null);
                $article->setContent($articleData['content'] ?? null);
                $article->setImageUrl($articleData['imageUrl'] ?? null);
                $article->setPublishDate(
                    isset($articleData['publishDate']) && $articleData['publishDate']
                        ? new \DateTime($articleData['publishDate'])
                        : new \DateTime()
                );
                $article->setIsFeatured($articleData['isFeatured'] ?? false);
            }
        }

        // Save gallery data
        if (isset($data['galleries']['items'])) {
            $galleryTypes = ['kitchen', 'bathroom', 'entry', 'dining', 'living', 'bedroom'];
            foreach ($galleryTypes as $type) {
                if (isset($data['galleries']['items'][$type])) {
                    $galleryData = $data['galleries']['items'][$type];
                    $gallery = $entityManager->getRepository(Gallery::class)->findOneBy(['roomType' => $type]);
                    if (!$gallery) {
                        $gallery = new Gallery();
                        $gallery->setRoomType($type);
                        $gallery->setCreatedAt(new \DateTimeImmutable());
                    }
                    $gallery->setName($galleryData['title'] ?? ucfirst($type) . ' Gallery');
                    $gallery->setDescription($galleryData['buttonText'] ?? 'VIEW MORE'); // Using buttonText as description
                    // Note: imageUrl is updated via uploadImage endpoint, not here
                    $entityManager->persist($gallery);
                }
            }
        }

        try {
            $entityManager->flush();
            return $this->json(['success' => true, 'message' => 'Changes saved successfully']);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Failed to save changes: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/home-editor/upload', name: 'admin_home_editor_upload', methods: ['POST'])]
    public function uploadImage(
        Request $request,
        EntityManagerInterface $entityManager,
        HomeContentRepository $homeContentRepository
    ): Response {
        $file = $request->files->get('image');
        $section = $request->request->get('section');
        $galleryType = $request->request->get('galleryType');
        $articleId = $request->request->get('articleId');

        if (!$file) {
            return $this->json(['success' => false, 'message' => 'No file uploaded'], 400);
        }

        try {
            $fileName = md5(uniqid()) . '.' . $file->guessExtension();
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/';
            $filePath = '/uploads/';

            if ($section === 'galleries') {
                $uploadDir .= 'galleries/';
                $filePath .= 'galleries/';
            } else {
                $uploadDir .= 'home/';
                $filePath .= 'home/';
            }

            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $file->move($uploadDir, $fileName);
            $filePath .= $fileName;

            if ($section === 'galleries' && $galleryType) {
                $gallery = $entityManager->getRepository(Gallery::class)->findOneBy(['roomType' => $galleryType]);
                if (!$gallery) {
                    $gallery = new Gallery();
                    $gallery->setRoomType($galleryType);
                    $gallery->setName(ucfirst($galleryType) . ' Gallery');
                    $gallery->setDescription('VIEW MORE');
                    $gallery->setCreatedAt(new \DateTimeImmutable());
                }
                $gallery->setImageUrl($filePath);
                $entityManager->persist($gallery);
                error_log("Gallery Type: $galleryType, Image URL: $filePath");
            } elseif ($articleId) {
                $article = $entityManager->getRepository(SpotlightArticle::class)->find($articleId);
                if ($article) {
                    $article->setImageUrl($filePath);
                    $entityManager->persist($article);
                }
            } else {
                $content = $homeContentRepository->findOneBy(['section' => $section]);
                if (!$content) {
                    $content = new HomeContent();
                    $content->setSection($section);
                    $entityManager->persist($content);
                }
                $content->setImage($filePath);
                $content->setUpdatedAt(new \DateTimeImmutable());
            }

            $entityManager->flush();

            return $this->json([
                'success' => true,
                'path' => $filePath,
                'message' => 'Image uploaded and saved successfully'
            ]);
        } catch (\Exception $e) {
            error_log("Upload failed: " . $e->getMessage());
            return $this->json([
                'success' => false,
                'message' => 'Failed to upload image: ' . $e->getMessage()
            ], 500);
        }
    }

    // Other methods (unchanged for brevity)
    #[Route('/home-editor', name: 'admin_home_editor')]
    public function homeEditor(
        ProductRepository $productRepository,
        HomeContentRepository $homeContentRepository,
        SpotlightArticleRepository $spotlightArticleRepository,
        EntityManagerInterface $entityManager
    ): Response
    {
        $allProducts = $productRepository->findAll();
        $featuredProducts = $productRepository->findBy(['isFeatured' => true], ['id' => 'ASC'], 6);
        $exploreProducts = $productRepository->findBy(['isExploreProduct' => true], ['id' => 'ASC'], 6);
        $content = $homeContentRepository->getAllContent();
        $galleries = $entityManager->getRepository(Gallery::class)->findAll();
        $spotlightArticles = $spotlightArticleRepository->findBy([], ['publishDate' => 'DESC'], 3);

        $defaultContent = [
            'hero' => ['image' => 'uploads/home/hero.jpg', 'title' => 'Luxury Collection', 'buttonText' => 'DISCOVER MORE'],
            'icons' => ['image' => 'uploads/home/icons-section.jpg', 'title' => 'Meet Our Icons', 'description' => '...', 'buttonText' => 'SHOP ICONS'],
            'cordless' => ['image' => 'uploads/home/cordless-section.jpg', 'title' => 'Cordless & Rechargeable Lamps', 'description' => '...', 'buttonText' => 'SHOP CORDLESS & RECHARGEABLES'],
            'spotlight' => ['image' => 'uploads/home/spotlight-section.jpg', 'title' => 'Spotlight Inspiration Galleries', 'description' => '...', 'buttonText' => 'VIEW GALLERIES'],
            'shop' => ['image' => 'uploads/home/shop-section.jpg', 'title' => 'Shop Our Collection', 'description' => '...', 'buttonText' => 'SHOP NOW'],
            'explore' => ['title' => 'Explore Our Products', 'buttonText' => 'EXPLORE MORE'],
        ];

        foreach ($defaultContent as $section => $defaults) {
            if (!isset($content[$section])) {
                $content[$section] = $defaults;
            }
        }

        return $this->render('admin/home_editor.html.twig', [
            'content' => $content,
            'featuredProducts' => $featuredProducts,
            'exploreProducts' => $exploreProducts,
            'allProducts' => $allProducts,
            'galleries' => $galleries,
            'spotlightArticles' => $spotlightArticles
        ]);
    }

    #[Route('/product/toggle-featured/{id}', name: 'admin_product_toggle_featured', methods: ['POST'])]
    public function toggleFeatured(Product $product, EntityManagerInterface $entityManager): Response
    {
        $product->setIsFeatured(!$product->isFeatured());
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'isFeatured' => $product->isFeatured()
        ]);
    }

    #[Route('/product/toggle-explore/{id}', name: 'admin_product_toggle_explore', methods: ['POST'])]
    public function toggleExplore(Product $product, EntityManagerInterface $entityManager, ProductRepository $productRepository): Response
    {
        if (!$product->isExploreProduct() && $productRepository->count(['isExploreProduct' => true]) >= 6) {
            return $this->json([
                'success' => false,
                'message' => 'Maximum of 6 products allowed in explore section'
            ], 400);
        }

        $product->setIsExploreProduct(!$product->isExploreProduct());
        $entityManager->flush();

        return $this->json([
            'success' => true,
            'isExploreProduct' => $product->isExploreProduct()
        ]);
    }

    #[Route('/product/update-order', name: 'admin_product_update_order', methods: ['POST'])]
    public function updateProductOrder(Request $request, EntityManagerInterface $entityManager): Response
    {
        $data = json_decode($request->getContent(), true);
        $order = $data['order'] ?? [];

        foreach ($order as $item) {
            $product = $entityManager->getRepository(Product::class)->find($item['id']);
            if ($product && $product->isFeatured()) {
                $product->setPosition($item['position']);
                $entityManager->persist($product);
            }
        }

        try {
            $entityManager->flush();
            return $this->json(['success' => true, 'message' => 'Product order updated successfully']);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Failed to update product order: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/dashboard', name: 'admin_dashboard')]
    public function index(ProductRepository $productRepository): Response
    {
        $stats = [
            'total_products' => $productRepository->count([]),
            'total_orders' => 0,
            'total_revenue' => 0,
            'active_users' => 0,
        ];

        return $this->render('admin/index.html.twig', [
            'stats' => $stats
        ]);
    }
}