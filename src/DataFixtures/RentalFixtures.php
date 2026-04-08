<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\Rental;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class RentalFixtures extends Fixture implements DependentFixtureInterface
{
    private Generator $faker;

	public function load(ObjectManager $manager): void
	{ 
        $this->faker = Factory::create();

        for ($i = 0; $i < 10; $i++) {
            $book = $this->getReference(BookFixtures::BOOK_REFERENCE . '_' . 1, Book::class);
           	$rental = new Rental();

            $rental->setCreatedAt(DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-30 days', 'now')));
            $rental->setUpdatedAt(DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-30 days', 'now')));
            $rental->setRentedAt(DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-30 days', 'now')));
            $book->addRental($rental);
            $rental->setOwner($this->getReference(UserFixtures::USER_REFERENCE . '_' . $this->faker->numberBetween(0, 2), User::class));
            // $rental->setBook($this->getReference(BookFixtures::BOOK_REFERENCE . '_' . $this->faker->numberBetween(0, 4), Book::class));

            $manager->persist($rental);
        }

        $manager->flush();
	}

    public function getDependencies(): array
    {
        return [BookFixtures::class, UserFixtures::class];
    }
}