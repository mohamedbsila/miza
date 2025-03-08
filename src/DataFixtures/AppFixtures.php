<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Subcategory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Create main categories
        $categories = [
            'Hanging' => 'Elegant hanging light fixtures for your space',
            'Wall' => 'Contemporary wall lighting solutions',
            'Standing' => 'Modern standing and floor lamps'
        ];

        $categoryEntities = [];
        foreach ($categories as $name => $description) {
            $category = new Category();
            $category->setName($name);
            $category->setSlug(strtolower($name));
            $category->setDescription($description);
            $manager->persist($category);
            $categoryEntities[$name] = $category;
        }

        // Create subcategories
        $subcategories = [
            'Hanging' => ['Chandeliers', 'Pendant Lights', 'Linear Suspension', 'Mini Pendants'],
            'Wall' => ['Wall Sconces', 'Bath & Vanity', 'Picture Lights', 'Swing Arm'],
            'Standing' => ['Floor Lamps', 'Table Lamps', 'Desk Lamps', 'Torchieres']
        ];

        foreach ($subcategories as $categoryName => $subs) {
            foreach ($subs as $subName) {
                $subcategory = new Subcategory();
                $subcategory->setName($subName);
                $subcategory->setSlug(strtolower(str_replace(' ', '-', $subName)));
                $subcategory->setCategory($categoryEntities[$categoryName]);
                $manager->persist($subcategory);
            }
        }

        $manager->flush();
    }
} 