<?php

namespace App\Repository;

use App\Entity\Product;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\QueryBuilder;

class ProductRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Product::class);
    }

    public function findByFilters(array $filters, string $sort = 'featured', ?string $query = null): array
    {
        $qb = $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c');

        // Apply search query if provided
        if ($query) {
            $qb->andWhere('p.name LIKE :query OR p.description LIKE :query OR p.sku LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }

        // Apply category filter
        if (!empty($filters['category'])) {
            $qb->andWhere('c.id = :category')
               ->setParameter('category', $filters['category']);
        }

        // Apply other filters
        $this->applyFilter($qb, 'finish', $filters['finish']);
        $this->applyFilter($qb, 'style', $filters['style']);
        $this->applyFilter($qb, 'height', $filters['height']);
        $this->applyFilter($qb, 'width', $filters['width']);
        $this->applyFilter($qb, 'designer', $filters['designer']);
        $this->applyFilter($qb, 'collection', $filters['collection']);
        $this->applyFilter($qb, 'lamping', $filters['lamping']);
        $this->applyFilter($qb, 'colorTemperature', $filters['color_temperature']);
        $this->applyFilter($qb, 'dimming', $filters['dimming']);
        $this->applyFilter($qb, 'inputVoltage', $filters['input_voltage']);

        // Apply price range filter
        if (!empty($filters['price_range'])) {
            if ($filters['price_range'] === '1000+') {
                $qb->andWhere('p.price >= :price_min')
                   ->setParameter('price_min', 1000);
            } else {
                list($min, $max) = explode('-', $filters['price_range']);
                $qb->andWhere('p.price >= :price_min AND p.price <= :price_max')
                   ->setParameter('price_min', $min)
                   ->setParameter('price_max', $max);
            }
        }

        // Apply sorting
        switch ($sort) {
            case 'price_asc':
                $qb->orderBy('p.price', 'ASC');
                break;
            case 'price_desc':
                $qb->orderBy('p.price', 'DESC');
                break;
            case 'newest':
                $qb->orderBy('p.createdAt', 'DESC');
                break;
            case 'featured':
            default:
                $qb->orderBy('p.isFeatured', 'DESC')
                   ->addOrderBy('p.createdAt', 'DESC');
                break;
        }

        return $qb->getQuery()->getResult();
    }

    private function applyFilter(QueryBuilder $qb, string $field, $value): void
    {
        if (!empty($value)) {
            $qb->andWhere("p.$field = :$field")
               ->setParameter($field, $value);
        }
    }

    public function getUniqueValues(string $field): array
    {
        $result = $this->createQueryBuilder('p')
            ->select("p.$field")
            ->where("p.$field IS NOT NULL")
            ->groupBy("p.$field")
            ->orderBy("p.$field", 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(function($item) use ($field) {
            return $item[$field];
        }, $result);
    }

    public function search(string $query)
    {
        return $this->createQueryBuilder('p')
            ->leftJoin('p.category', 'c')
            ->where('p.name LIKE :query')
            ->orWhere('p.description LIKE :query')
            ->orWhere('p.sku LIKE :query')
            ->orWhere('c.name LIKE :query')
            ->setParameter('query', '%' . $query . '%')
            ->orderBy('p.name', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    public function findSimilarProducts(string $query)
    {
        // Get all product names
        $products = $this->createQueryBuilder('p')
            ->select('p.name')
            ->getQuery()
            ->getResult();

        $suggestions = [];
        foreach ($products as $product) {
            $similarity = $this->calculateSimilarity($query, strtolower($product['name']));
            if ($similarity > 0.4) { // Threshold for similarity
                $suggestions[] = [
                    'name' => $product['name'],
                    'similarity' => $similarity
                ];
            }
        }

        // Sort by similarity
        usort($suggestions, function($a, $b) {
            return $b['similarity'] <=> $a['similarity'];
        });

        // Return top 3 suggestions
        return array_slice($suggestions, 0, 3);
    }

    private function calculateSimilarity(string $str1, string $str2): float
    {
        $str1 = strtolower($str1);
        $str2 = strtolower($str2);
        
        // Levenshtein distance
        $levDistance = levenshtein($str1, $str2);
        $maxLength = max(strlen($str1), strlen($str2));
        
        if ($maxLength === 0) {
            return 0;
        }

        // Convert distance to similarity score (0 to 1)
        return 1 - ($levDistance / $maxLength);
    }
} 