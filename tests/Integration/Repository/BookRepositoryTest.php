<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Book;
use App\Entity\Category;
use App\Entity\Rental;
use App\Entity\User;
use App\Repository\BookRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use DoctrineExtensions\Query\Mysql\Date;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BookRepositoryTest extends KernelTestCase
{
    public function testAvailableBookWithReturnedDate(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $category = $this->createCategory();
        $user = $this->createUser();
        $book = $this->createBook();
        $rental = $this->createRental();

        $book->setCategory($category);
        $rental->setOwner($user);
        $rental->setReturnedAt(new DateTimeImmutable());
        $rental->setBook($book);

        $bookRepository = $container->get(BookRepository::class);
        $entityManager = $container->get(EntityManagerInterface::class);

        $entityManager->persist($book);
        $entityManager->persist($rental);
        $entityManager->persist($category);
        $entityManager->persist($user);

        $entityManager->flush();

        
        $result = $bookRepository->queryAvailable()->getQuery()->getResult();

        $this->assertContains($book, $result);
    }

    public function testAvailableBookWithNoRentals(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $bookRepository = $container->get(BookRepository::class);
        $entityManager = $container->get(EntityManagerInterface::class);

        $book = $this->createBook();
        $user = $this->createUser();
        $category = $this->createCategory();

        $book->setCategory($category);

        $entityManager->persist($book);
        $entityManager->persist($category);
        $entityManager->persist($user);

        $entityManager->flush();

        $result = $bookRepository->queryAvailable()->getQuery()->getResult();

        $this->assertContains($book, $result);
    }

    public function testNotAvailableBookWithCurrentRental(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $bookRepository = $container->get(BookRepository::class);
        $entityManager = $container->get(EntityManagerInterface::class);

        $book = $this->createBook();
        $user = $this->createUser();
        $category = $this->createCategory();
        $rental = $this->createRental();
        $rental->setBook($book);
        $rental->setOwner($user);
        
        $book->setCategory($category);

        $entityManager->persist($book);
        $entityManager->persist($category);
        $entityManager->persist($user);
        $entityManager->persist($rental);

        $entityManager->flush();

        $result = $bookRepository->queryAvailable()->getQuery()->getResult();

        $this->assertNotContains($book, $result);
    }

    public function testReturnsTrueWhenBookIsCurrentlyRented(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $book = $this->createBook();
        $category = $this->createCategory();
        $user = $this->createUser();
        $rental = $this->createRental();

        $rental->setBook($book);
        $rental->setOwner($user);
        $book->setCategory($category);

        $bookRepository = $container->get(BookRepository::class);
        $entityManager = $container->get(EntityManagerInterface::class);

        $entityManager->persist($book);
        $entityManager->persist($category);
        $entityManager->persist($user);
        $entityManager->persist($rental);

        $entityManager->flush();


        $result = $bookRepository->isCurrentlyRented($book);

        $this->assertTrue($result);
    }

    public function testReturnsFalseWhenBookIsNotCurrentlyRented(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $book = $this->createBook();
        $category = $this->createCategory();
        $user = $this->createUser();
        $rental = $this->createRental();

        $rental->setBook($book);
        $rental->setOwner($user);
        $book->setCategory($category);
        $rental->setReturnedAt(new DateTimeImmutable());

        $bookRepository = $container->get(BookRepository::class);
        $entityManager = $container->get(EntityManagerInterface::class);

        $entityManager->persist($book);
        $entityManager->persist($category);
        $entityManager->persist($user);
        $entityManager->persist($rental);

        $entityManager->flush();

        $result = $bookRepository->isCurrentlyRented($book);

        $this->assertFalse($result);
    }

    public function testReturnsNewestBook(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $book = $this->createBook();
        $book->setCreatedAt(new DateTimeImmutable('yesterday'));
        $newestBook = $this->createBook();

        $bookRepository = $container->get(BookRepository::class);
        $entityManager = $container->get(EntityManagerInterface::class);

        $entityManager->persist($book);
        $entityManager->persist($newestBook);
        $entityManager->flush();

        $result = $bookRepository->queryNewest();

        $this->assertSame($newestBook, $result);
        $this->assertNotSame($book, $result);
    }

    public function testReturnsBooksByGivenCategory(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $bookAdventureIncluded = $this->createBook();
        $bookFantasyIncluded = $this->createBook();
        $bookExcluded = $this->createBook();

        $categoryIncluded = $this->createCategory();
        $categoryExcluded = $this->createCategory();

        $bookAdventureIncluded->setCategory($categoryIncluded);
        $bookFantasyIncluded->setCategory($categoryIncluded);
        $bookExcluded->setCategory($categoryExcluded);

        $entityManager = $container->get(EntityManagerInterface::class);
        $bookRepository = $container->get(BookRepository::class);

        $entityManager->persist($bookAdventureIncluded);
        $entityManager->persist($bookFantasyIncluded);
        $entityManager->persist($bookExcluded);
        $entityManager->persist($categoryIncluded);
        $entityManager->persist($categoryExcluded);
        $entityManager->flush();

        $result = $bookRepository->findByCategory($categoryIncluded);

        $this->assertContains($bookAdventureIncluded, $result);
        $this->assertContains($bookFantasyIncluded, $result);
        $this->assertNotContains($bookExcluded, $result);

    }

    public function testReturnsMostRentedBooksUpToGivenLimit(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $mostRentedBook = $this->createBook();
        $user = $this->createUser();

        $entityManager = $container->get(EntityManagerInterface::class);
        $bookRepository = $container->get(BookRepository::class);

        for ($i = 0; $i < 3; $i++) {
            $rental = $this->createRental();
            $rental->setBook($mostRentedBook);
            $rental->setReturnedAt(new DateTimeImmutable(" - 1 year {$i} month"));
            $rental->setOwner($user);
            $entityManager->persist($rental);
        }

        $bookWithOneRental = $this->createBook();
        $rental = $this->createRental();
        $rental->setBook($bookWithOneRental);
        $rental->setOwner($user);

        $entityManager->persist($mostRentedBook);
        $entityManager->persist($bookWithOneRental);
        $entityManager->persist($user);
        $entityManager->persist($rental);
        $entityManager->flush();

        $result = $bookRepository->queryMostRentedByGivenLimit(1);

        $this->assertSame($mostRentedBook, $result[0][0]);
        $this->assertNotSame($bookWithOneRental, $result[0][0]);
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

    private function createCategory(): Category
    {
        $category = new Category();
        $category->setTitle('Test Category');
        $category->setColor('#dddddd');

        return $category;
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

    private function createUser(): User
    {
        $user = new User();
        $user->setEmail('test@example.com');
        $user->setPassword('test123');

        return $user;
    }
}