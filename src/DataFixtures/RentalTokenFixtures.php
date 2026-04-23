<?php

namespace App\DataFixtures;

use App\Entity\BookQueue;
use App\Entity\RentalToken;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class RentalTokenFixtures extends Fixture implements DependentFixtureInterface
{
    private Generator $faker;

	public function load(ObjectManager $manager): void
	{
        $this->faker = Factory::create();

        for ($i = 0; $i < 5; $i++) {
            $bookQueue = $this->getReference(BookQueueFixtures::BOOK_QUEUE_REFERENCE . '_' . $i, BookQueue::class);
            
		    $rentalToken = new RentalToken();
            $rentalToken->setCreatedAt(DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-30 days', 'now')));
            $rentalToken->setContent(bin2hex(random_bytes(32)));
            $rentalToken->setExpirationDate(DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-20 days', '+24 hours')));
            $rentalToken->setBook($bookQueue->getBook());
            $rentalToken->setUser($bookQueue->getUser());

            $manager->persist($rentalToken);
        }

        $manager->flush();
	}

    public function getDependencies(): array
    {
        return [BookQueueFixtures::class];
    }
}
