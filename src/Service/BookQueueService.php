<?php

namespace App\Service;

use App\Entity\Book;
use App\Entity\BookQueue;
use App\Entity\RentalToken;
use App\Entity\User;
use App\Repository\BookQueueRepository;
use DateTimeImmutable;

class BookQueueService
{
    public function __construct(private BookQueueRepository $bookQueueRepository) {}
    
    public function createQueuedBook(Book $book, User $user): BookQueue
    {
        $bookQueue = new BookQueue();
        $bookQueue->setBook($book);
        $bookQueue->setUser($user);
        $bookQueue->setMissingOpportunity(0);
        $bookQueue->setCreatedAt(new DateTimeImmutable());
        $this->assignPosition($bookQueue, $book);

        $this->bookQueueRepository->save($bookQueue);

        return $bookQueue;
    }

    public function assignPosition(BookQueue $bookQueue, Book $book): void
    {
        $queuedBooksPositions = $this->bookQueueRepository->queryQueuedBooks($book);
        $bookQueue->setPosition(count($queuedBooksPositions) + 1);
    }

    public function recountQueue(Book $book): void
    {
        $queueList = $this->bookQueueRepository->findBy(
            [
                'book' => $book
            ]            
        );

        foreach ($queueList as $queue) {
            $queue->decrementPosition();
            $this->bookQueueRepository->save($queue);
        }
    }

    public function getTheFirstPositionInQueue(Book $book): ?BookQueue
    {
        return $this->bookQueueRepository->findOneBy([
            'book' => $book
        ]);
    }

    public function deletePositionFromQueue(Book $book, User $user): void
    {
        $position = $this->bookQueueRepository->findOneBy([
            'user' => $user,
            'book' => $book
        ]);

        $this->bookQueueRepository->delete($position);
    }

    public function queuedBookWithExpiredToken(RentalToken $rentalToken): ?BookQueue
    {
        return $this->bookQueueRepository->findOneBy([
            'user' => $rentalToken->getUser(),
            'book' => $rentalToken->getBook()
        ]);
    }

    public function increaseMissingOpportunity(BookQueue $bookQueue): void
    {
        $bookQueue->incrementMissingOpportunity();
        $this->bookQueueRepository->save($bookQueue);
    }

    public function deleteBookQueueWithLimitExceeded(BookQueue $bookQueue): void
    {
        $this->bookQueueRepository->delete($bookQueue);
    }
}
