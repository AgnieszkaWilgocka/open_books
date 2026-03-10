<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\Category;
use App\Enum\BookStatusEnum;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;


class BookFixtures extends Fixture implements DependentFixtureInterface
{
    private Generator $faker;

	public function load(ObjectManager $manager): void
	{
        $this->faker = Factory::create();

		for ($i = 0; $i < 5; $i++) {
            $book = new Book();
            $book->setTitle($this->faker->sentence(2));
            $book->setCreatedAt(DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-30 days', 'now')));
            $book->setUpdatedAt(DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-30 days', 'now')));
            $book->setYearOfRelease($this->faker->numberBetween(1900, 2026));
            $book->setPages($this->faker->numberBetween(100, 500));
            $book->setStatus(BookStatusEnum::Available);
            $book->setCategory($this->getReference(CategoryFixtures::CATEGORY_REFERENCE . '_' . $this->faker->numberBetween(0, 9), Category::class));

            $manager->persist($book);
        }

        $manager->flush();
	}

    public function getDependencies(): array
    {
        return [CategoryFixtures::class];
    }
}