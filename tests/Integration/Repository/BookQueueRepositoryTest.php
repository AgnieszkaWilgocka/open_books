<?php

namespace App\Tests\Integration\Repository;

use App\Entity\Book;
use App\Entity\BookQueue;
use App\Entity\User;
use App\Repository\BookQueueRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BookQueueRepositoryTest extends KernelTestCase
{
    public function testReturnsTrueWhenUserIsInWaitingList(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $bookQueue = new BookQueue();
        $book = $this->createBook();
        $user = $this->createUser();

        $bookQueue->setBook($book);
        $bookQueue->setUser($user);
        $bookQueue->setCreatedAt(new DateTimeImmutable());
        $bookQueue->setMissingOpportunity(1);

        $bookQueueRepository = $container->get(BookQueueRepository::class);
        $entityManager = $container->get(EntityManagerInterface::class);

        $entityManager->persist($book);
        $entityManager->persist($user);
        $entityManager->persist($bookQueue);

        $entityManager->flush();

        $result = $bookQueueRepository->isUserInWaitingList($user, $book);

        $this->assertTrue($result);
    }

    public function testReturnsFalseWhenUserIsNotInWaitingList(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $user = $this->createUser();
        $userNotBelonging = new User()->setPassword('123test')->setEmail('123test@example.com');
        
        $book = $this->createBook();
        $bookQueue = new BookQueue();
        $bookQueue->setCreatedAt(new DateTimeImmutable());
        $bookQueue->setMissingOpportunity(1);
        $bookQueue->setBook($book);
        $bookQueue->setUser($user);

        $bookQueueRepository = $container->get(BookQueueRepository::class);
        $entityManager = $container->get(EntityManagerInterface::class);

        $entityManager->persist($user);
        $entityManager->persist($userNotBelonging);
        $entityManager->persist($book);
        $entityManager->persist($bookQueue);

        $entityManager->flush();
        

        $result = $bookQueueRepository->isUserInWaitingList($userNotBelonging, $book);

        $this->assertFalse($result);
    }

    public function testReturnsFirstPositionInQueue(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $book = $this->createBook();
        $user = $this->createUser();
        $bookQueue = new BookQueue();
        $bookQueue->setBook($book);
        $bookQueue->setCreatedAt(new DateTimeImmutable());
        $bookQueue->setMissingOpportunity(1);
        $bookQueue->setPosition(1);
        $bookQueue->setUser($user);

        $bookQueueRepository = $container->get(BookQueueRepository::class);
        $entityManager = $container->get(EntityManagerInterface::class);
        $entityManager->persist($book);
        $entityManager->persist($bookQueue);
        $entityManager->persist($user);
        $entityManager->flush();

        $result = $bookQueueRepository->getFirstPosition($book);

        $this->assertEquals($bookQueue, $result);
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