<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Book;
use App\Entity\Rental;
use App\Entity\User;
use App\Repository\RentalRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RentalRepositoryTest extends KernelTestCase
{
    public function testReturnsActiveRentalsForGivenUser(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $bookFantasy = $this->createBook();
        $bookAdventure = $this->createBook();

        $user = $this->createUser();
        $rentalFantasy = $this->createRental();
        $rentalAdventure = $this->createRental();

        $rentalFantasy->setBook($bookFantasy);
        $rentalAdventure->setBook($bookAdventure);
        $rentalFantasy->setOwner($user);
        $rentalAdventure->setOwner($user);

        $bookExcluded = $this->createBook();
        $userExcluded = new User()->setEmail('123@example.com')->setPassword('123123');
        $rentalExcluded = $this->createRental();
        $rentalExcluded->setBook($bookExcluded);
        $rentalExcluded->setOwner($userExcluded);

        $rentalRepository = $container->get(RentalRepository::class);
        $entityManager = $container->get(EntityManagerInterface::class);

        $entityManager->persist($user);
        $entityManager->persist($bookFantasy);
        $entityManager->persist($bookAdventure);
        $entityManager->persist($rentalFantasy);
        $entityManager->persist($rentalAdventure);
        $entityManager->persist($bookExcluded);
        $entityManager->persist($userExcluded);
        $entityManager->persist($rentalExcluded);

        $entityManager->flush();

        $result = $rentalRepository->queryActiveForUser($user);

        $this->assertContains($rentalFantasy, $result);
        $this->assertContains($rentalAdventure, $result);
        $this->assertNotContains($rentalExcluded, $result);
    }

    private function createUser(): User
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('test123');

        return $user;
    }

    private function createBook(): Book
    {
        $book = new Book();
        $book->setTitle('Test Book');
        $book->setCreatedAt(new DateTimeImmutable());
        $book->setUpdatedAt(new DateTimeImmutable());
        $book->setYearOfRelease(1900);
        $book->setPages(20);
        $book->setWriter('Test');
        $book->setDescription('test');

        return $book;
    }

    private function createRental(): Rental
    {
        $rental = new Rental();
        $rental->setRentedAt(new DateTimeImmutable());
        $rental->setCreatedAt(new DateTimeImmutable());
        $rental->setUpdatedAt(new DateTimeImmutable());
        $rental->setDeadline(new DateTimeImmutable());

        return $rental;
    }
}