<?php

namespace App\Command;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class LoadProductsCommand extends Command
{
    protected static $defaultName = 'app:load-products';
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected function configure(): void
    {
        $this->setDescription('Loads initial product data into the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $products = [
            [
                'name' => 'Boite Aurea BLANCHE',
                'price' => 89.00,
                'image' => 'assets/images/products/boite-aurea-blanche.jpeg',
                'isNew' => true,
                'sku' => 'BAB-001'
            ],
            [
                'name' => 'Boite Aurea GRISE',
                'price' => 89.00,
                'image' => 'assets/images/products/boite-aurea-grise.jpeg',
                'isNew' => true,
                'sku' => 'BAG-002'
            ],
            [
                'name' => 'Boite Aurea Noire',
                'price' => 89.00,
                'image' => 'assets/images/products/boite-aurea-noire.jpeg',
                'isNew' => true,
                'sku' => 'BAN-003'
            ],
            [
                'name' => 'Boite Hako15_Shodo',
                'price' => 125.00,
                'image' => 'assets/images/products/boite-hako15-shodo.jpeg',
                'isNew' => true,
                'sku' => 'BHS-004'
            ],
            [
                'name' => 'Boite Decorative',
                'price' => 95.00,
                'image' => 'assets/images/products/boite-decorative.jpeg',
                'isNew' => true,
                'sku' => 'BDC-005'
            ],
            [
                'name' => 'Boite Design',
                'price' => 110.00,
                'image' => 'assets/images/products/boite-design.jpeg',
                'isNew' => true,
                'sku' => 'BDS-006'
            ],
        ];

        foreach ($products as $productData) {
            $product = new Product();
            $product->setName($productData['name']);
            $product->setPrice($productData['price']);
            $product->setImage($productData['image']);
            $product->setIsNew($productData['isNew']);
            $product->setSku($productData['sku']);

            $this->entityManager->persist($product);
        }

        $this->entityManager->flush();

        $io->success(sprintf('Successfully loaded %d products!', count($products)));

        return Command::SUCCESS;
    }
} 