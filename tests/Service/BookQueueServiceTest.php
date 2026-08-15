<?php

use App\Entity\Book;
use App\Entity\BookQueue;
use App\Entity\User;
use App\Repository\BookQueueRepository;
use App\Service\BookQueueService;
use PHPUnit\Framework\TestCase;

class BookQueueServiceTest extends TestCase
{
    public function testAssigningQueuePosition()
    {
        $bookQueue = new BookQueue();
        $book = new Book();
        $bookQueueRepositoryStub = $this->createStub(BookQueueRepository::class);

        $bookQueueRepositoryStub
            ->method('queryQueuedBooks')
            ->willReturn([$bookQueue]);

        $service = new BookQueueService($bookQueueRepositoryStub);

        $service->assignPosition($bookQueue, $book);

        $this->assertSame(2, $bookQueue->getPosition());
    }

    public function testAssigningBookPositionWhenQueueIsEmpty()
    {
        $bookQueue = new BookQueue();
        $book = new Book();

        $bookQueueRepositoryStub = $this->createStub(BookQueueRepository::class);

        $bookQueueRepositoryStub
            ->method('queryQueuedBooks')
            ->willReturn([]);

        $service = new BookQueueService($bookQueueRepositoryStub);

        $service->assignPosition($bookQueue, $book);

        $this->assertSame(1, $bookQueue->getPosition());
    }

    public function testPrepareQueuedBooksDataWithNoUser()
    {
        $user = null;
        $bookQueueRepositoryMock = $this->createMock(BookQueueRepository::class);

        $bookQueueRepositoryMock
            ->expects($this->never())
            ->method('findBy');

        $bookQueueRepositoryMock
            ->expects($this->once())
            ->method('queryAll')
            ->willReturn([]);

        $service = new BookQueueService($bookQueueRepositoryMock);

        $result = $service->prepareQueuedBooksData($user);

        $this->assertArrayHasKey('queuedUserBooksIds', $result);
        $this->assertArrayHasKey('queuedBooksIds', $result);
        $this->assertEmpty($result['queuedUserBooksIds']);

    }

    // public function testRecountQueueWithOneBookQueue()
    // {
    //     $book = new Book();
    //     $bookQueue = new BookQueue();
    //     $bookQueue->setBook($book);
    //     $bookQueue->setPosition(3);

    //     $bookQueueRepositoryMock = $this->createMock(BookQueueRepository::class);

    //     $bookQueueRepositoryMock
    //         ->expects($this->once())
    //         ->method('findBy')
    //         ->willReturn([$bookQueue]); 

    //     $bookQueueRepositoryMock
    //         ->expects($this->once())
    //         ->method('save');
        
    //     $service = new BookQueueService($bookQueueRepositoryMock);

    //     $service->recountQueue($book);

    //     $this->assertSame(2, $bookQueue->getPosition());
    // }

    public function testRecountQueueDecrementsPosition()
    {
        $book = new Book();

        $bookQueuesArray = [];

        for ($i = 2; $i <= 4; $i++) {
            $bookQueue = new BookQueue();
            $bookQueue->setBook($book);
            $bookQueue->setPosition($i);
            $bookQueuesArray[] = $bookQueue;
        }

        $bookQueueRepositoryMock = $this->createMock(BookQueueRepository::class);

        $bookQueueRepositoryMock
            ->expects($this->once())
            ->method('findBy')
            ->willReturn($bookQueuesArray);

        $bookQueueRepositoryMock    
            ->expects($this->exactly(count($bookQueuesArray)))
            ->method('save');
        
        $service = new BookQueueService($bookQueueRepositoryMock);

        $service->recountQueue($book);

        foreach($bookQueuesArray as $index => $bookQueue) {
            $this->assertSame($index + 1, $bookQueue->getPosition());
        }
    }

    public function testRecountQueueWhenBookQueueIsEmpty()
    {
        $book = new Book();

        $bookQueueRepositoryMock = $this->createMock(BookQueueRepository::class);

        $bookQueueRepositoryMock
            ->expects($this->once())
            ->method('findBy')
            ->willReturn([]);

        $bookQueueRepositoryMock
            ->expects($this->never())
            ->method('save');

        $service = new BookQueueService($bookQueueRepositoryMock);

        $service->recountQueue($book);
    }

    public function testIncrementationOfMissedOpportunity()
    {
        $bookQueue = new BookQueue();
        $bookQueue->setMissingOpportunity(0);

        $bookQueueRepositoryMock = $this->createMock(BookQueueRepository::class);

        $bookQueueRepositoryMock
            ->expects($this->once())
            ->method('save');

        $service = new BookQueueService($bookQueueRepositoryMock);

        $service->increaseMissingOpportunity($bookQueue);

        $this->assertSame(1, $bookQueue->getMissingOpportunity());
    }
}