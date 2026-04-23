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
    public const BOOK_QUEUE_REFERENCE = 'BOOK_QUEUE';

    private Generator $faker;

	public function load(ObjectManager $manager): void
	{
        $this->faker = Factory::create();

        for ($i = 0; $i < 5; $i++) {
		    $bookQueue = new BookQueue();
            $bookQueue->setCreatedAt(DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-30 days', 'now')));
            $bookQueue->setMissingOpportunity($this->faker->numberBetween(0, 3));
            $bookQueue->setBook($this->getReference(BookFixtures::BOOK_REFERENCE . '_' . $i, Book::class));
            $bookQueue->setUser($this->getReference(UserFixtures::USER_REFERENCE . '_' . $this->faker->numberBetween(0, 2), User::class));
            $bookQueue->setPosition($i + 1);

            $this->setReference(self::BOOK_QUEUE_REFERENCE . '_' . $i, $bookQueue);
            $manager->persist($bookQueue);
        }

        $manager->flush();
	}

    public function getDependencies(): array
    {
        return [BookFixtures::class, UserFixtures::class];
    }
}
