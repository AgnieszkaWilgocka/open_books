<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Book;
use App\Entity\RentalToken;
use App\Entity\User;
use App\Repository\RentalTokenRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RentalTokenRepositoryTest extends KernelTestCase
{
    public function testReturnsExpiredToken(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $rentalToken = $this->createRentalToken();
        $book = $this->createBook();
        $user = $this->createUser();
        $rentalToken->setBook($book);
        $rentalToken->setUser($user);
        $rentalToken->setExpirationDate(new DateTimeImmutable('yesterday'));

        $rentalTokenRepository = $container->get(RentalTokenRepository::class);
        $entityManager = $container->get(EntityManagerInterface::class);

        $entityManager->persist($book);
        $entityManager->persist($user);
        $entityManager->persist($rentalToken);

        $entityManager->flush();

        $result = $rentalTokenRepository->queryExpiredTokens();

        $this->assertContains($rentalToken, $result);
    }

    private function createRentalToken(): RentalToken
    {
        $rentalToken = new RentalToken();
        $rentalToken->setContent('test');
        $rentalToken->setCreatedAt(new DateTimeImmutable());

        return $rentalToken;
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

    private function createUser(): User
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('test123');

        return $user;
    }
}