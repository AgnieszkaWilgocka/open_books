<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\FavoriteCategory;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class FavoriteCategoryFixtures extends Fixture implements DependentFixtureInterface
{
    private Generator $faker;
    const FAVORITE_CATEGORY_REFERENCE = 'fav_category';

    public function load(ObjectManager $manager): void
	{
        $this->faker = Factory::create();

        for ($i = 0; $i < 10; $i++) {
		    $favCategory = new FavoriteCategory();
            $favCategory->setCreatedAt(DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-30 days', 'now')));
            $favCategory->setUpdatedAt(DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-30 days', 'now')));
            $favCategory->setNotificationsEnabled($this->faker->boolean(50));
            $favCategory->setCategory($this->getReference(CategoryFixtures::CATEGORY_REFERENCE . '_' . $this->faker->numberBetween(0, 9), Category::class));
            $favCategory->setOwner($this->getReference(UserFixtures::USER_REFERENCE . '_' . $this->faker->numberBetween(0, 2), User::class));
            
            $manager->persist($favCategory);
        }

        $manager->flush();
	}

    public function getDependencies(): array
    {
        return [UserFixtures::class, CategoryFixtures::class];
    }
}
