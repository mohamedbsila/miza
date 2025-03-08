<?php

namespace App\Repository;

use App\Entity\Gallery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


class GalleryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gallery::class);
    }

    public function findAll(): array
    {
        return $this->findBy([], ['id' => 'ASC']);
    }

    public function findByRoomType(string $roomType): array
    {
        return $this->createQueryBuilder('g')
            ->andWhere('g.roomType = :roomType')
            ->setParameter('roomType', $roomType)
            ->orderBy('g.id', 'ASC')
            ->getQuery()
            ->getResult();
    }
}