<?php

namespace App\Repository;

use App\Entity\HomeContent;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HomeContent>
 */
class HomeContentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HomeContent::class);
    }

    public function findBySection(string $section): ?HomeContent
    {
        return $this->findOneBy(['section' => $section]);
    }

    public function getAllContent(): array
    {
        $content = [];
        $sections = [
            'hero',
            'icons',
            'cordless',
            'spotlight',
            'shop',
            'explore',
            'galleries'
        ];

        foreach ($sections as $section) {
            $sectionContent = $this->findBySection($section);
            if ($sectionContent) {
                if ($section === 'galleries') {
                    $content[$section] = [
                        'title' => $sectionContent->getTitle(),
                        'kitchen' => [
                            'image' => $sectionContent->getImage(),
                            'title' => 'Kitchen Gallery',
                            'buttonText' => 'VIEW MORE'
                        ],
                        'bathroom' => [
                            'image' => $sectionContent->getImage(),
                            'title' => 'Bathroom Gallery',
                            'buttonText' => 'VIEW MORE'
                        ],
                        'entry' => [
                            'image' => $sectionContent->getImage(),
                            'title' => 'Entry & Hall Gallery',
                            'buttonText' => 'VIEW MORE'
                        ],
                        'dining' => [
                            'image' => $sectionContent->getImage(),
                            'title' => 'Dining Room Gallery',
                            'buttonText' => 'VIEW MORE'
                        ],
                        'living' => [
                            'image' => $sectionContent->getImage(),
                            'title' => 'Living Room Gallery',
                            'buttonText' => 'VIEW MORE'
                        ],
                        'bedroom' => [
                            'image' => $sectionContent->getImage(),
                            'title' => 'Bedroom Gallery',
                            'buttonText' => 'VIEW MORE'
                        ]
                    ];
                } else {
                    $content[$section] = [
                        'image' => $sectionContent->getImage(),
                        'title' => $sectionContent->getTitle(),
                        'description' => $sectionContent->getDescription(),
                        'buttonText' => $sectionContent->getButtonText(),
                    ];
                }
            }
        }

        return $content;
    }
} 