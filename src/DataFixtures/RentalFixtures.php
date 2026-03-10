<?php

namespace App\DataFixtures;

use App\Entity\Rental;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

class RentalFixtures extends Fixture
{
    private Generator $faker;

	public function load(ObjectManager $manager): void
	{ 
        $this->faker = Factory::create();

        for ($i = 0; $i < 10; $i++) {
           	$rental = new Rental();

            $rental->setCreatedAt(DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-30 days', 'now')));
            $rental->setUpdatedAt(DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-30 days', 'now')));

            $manager->persist($rental);
        }

        $manager->flush();
	}
}