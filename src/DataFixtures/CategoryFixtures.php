<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class CategoryFixtures extends Fixture
{
    private Generator $faker;
    const CATEGORY_REFERENCE = 'category';

	public function load(ObjectManager $manager): void
	{
        $this->faker = Factory::create();

        for ($i = 0; $i < 10; $i++) {
		    $category = new Category();
            $category->setTitle($this->faker->colorName());
            $category->setColor(sprintf('%06x', random_int(0, 0xFFFFFF)));
            
            $manager->persist($category);
            $this->addReference(self::CATEGORY_REFERENCE . '_' . $i, $category);
        }

        $manager->flush();
	}
}
