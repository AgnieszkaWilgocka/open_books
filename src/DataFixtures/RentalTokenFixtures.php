<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\RentalToken;
use App\Entity\User;
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

        for ($i = 0; $i < 10; $i++) {
            
		    $rentalToken = new RentalToken();
            $rentalToken->setCreatedAt(DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-30 days', 'now')));
            $rentalToken->setContent(bin2hex(random_bytes(32)));
            $rentalToken->setExpirationDate(DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('now', '+24 hours')));
            $rentalToken->setBook($this->getReference(BookFixtures::BOOK_REFERENCE . '_' . $this->faker->numberBetween(0, 4), Book::class));
            $rentalToken->setUser($this->getReference(UserFixtures::USER_REFERENCE . '_' . $this->faker->numberBetween(0, 2), User::class));

            $manager->persist($rentalToken);
        }

        $manager->flush();
	}

    public function getDependencies(): array
    {
        return [BookFixtures::class, UserFixtures::class];
    }
}
