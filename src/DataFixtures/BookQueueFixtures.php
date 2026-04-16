<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\BookQueue;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class BookQueueFixtures extends Fixture implements DependentFixtureInterface
{
    private Generator $faker;

	public function load(ObjectManager $manager): void
	{
        $this->faker = Factory::create();

        for ($i = 0; $i < 10; $i++) {
		    $bookQueue = new BookQueue();
            $bookQueue->setCreatedAt(DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-30 days', 'now')));
            $bookQueue->setMissingOpportunity($this->faker->numberBetween(0, 3));
            $bookQueue->setBook($this->getReference(BookFixtures::BOOK_REFERENCE . '_' . $this->faker->numberBetween(0, 4), Book::class));
            $bookQueue->setUser($this->getReference(UserFixtures::USER_REFERENCE . '_' . $this->faker->numberBetween(0, 2), User::class));

            $manager->persist($bookQueue);
        }

        $manager->flush();
	}

    public function getDependencies(): array
    {
        return [BookFixtures::class, UserFixtures::class];
    }
}
