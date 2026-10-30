<?php

use App\Entity\Book;
use App\Entity\BookQueue;
use App\Entity\User;
use App\Repository\BookQueueRepository;
use App\Service\BookQueueService;
use PHPUnit\Framework\TestCase;

class BookQueueServiceTest extends TestCase
{

    private Book $book;

    public function testIsBookQueueCreated(): void
    {
        $user = new User();
        $bookQueueRepositoryMock = $this->createMock(BookQueueRepository::class);

        $bookQueueRepositoryMock
            ->expects($this->once())
            ->method('save');
        
        $bookQueueService = new BookQueueService($bookQueueRepositoryMock);
        $bookQueue = $bookQueueService->createQueuedBook($this->book, $user);

        $this->assertInstanceOf(BookQueue::class, $bookQueue);
        $this->assertNotNull($bookQueue->getBook());
        $this->assertNotNull($bookQueue->getUser());
        $this->assertSame($this->book, $bookQueue->getBook());
        $this->assertSame($user, $bookQueue->getUser());
        $this->assertNotNull($bookQueue->getMissingOpportunity());
        $this->assertSame(0, $bookQueue->getMissingOpportunity());
        $this->assertNotNull($bookQueue->getCreatedAt());
        $this->assertInstanceOf(DateTimeImmutable::class, $bookQueue->getCreatedAt());
        $this->assertNotNull($bookQueue->getPosition());
    }

    public function testAssigningQueuePosition(): void
    {
        $bookQueue = new BookQueue();
        $bookQueueRepositoryStub = $this->createStub(BookQueueRepository::class);

        $bookQueueRepositoryStub
            ->method('queryQueuedBooks')
            ->willReturn([$bookQueue]);

        $service = new BookQueueService($bookQueueRepositoryStub);

        $service->assignPosition($bookQueue, $this->book);

        $this->assertSame(2, $bookQueue->getPosition());
    }

    public function testAssigningBookPositionWhenQueueIsEmpty(): void
    {
        $bookQueue = new BookQueue();
        $bookQueueRepositoryStub = $this->createStub(BookQueueRepository::class);

        $bookQueueRepositoryStub
            ->method('queryQueuedBooks')
            ->willReturn([]);

        $service = new BookQueueService($bookQueueRepositoryStub);

        $service->assignPosition($bookQueue, $this->book);

        $this->assertSame(1, $bookQueue->getPosition());
    }

    public function testPrepareQueuedBooksDataWithNoUser(): void
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

    public function testRecountQueueDecrementsPosition(): void
    {
        $bookQueuesArray = [];

        for ($i = 2; $i <= 4; $i++) {
            $bookQueue = new BookQueue();
            $bookQueue->setBook($this->book);
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

        $service->recountQueue($this->book);

        foreach($bookQueuesArray as $index => $bookQueue) {
            $this->assertSame($index + 1, $bookQueue->getPosition());
        }
    }

    public function testRecountQueueWhenBookQueueIsEmpty(): void
    {
        $bookQueueRepositoryMock = $this->createMock(BookQueueRepository::class);

        $bookQueueRepositoryMock
            ->expects($this->once())
            ->method('findBy')
            ->willReturn([]);

        $bookQueueRepositoryMock
            ->expects($this->never())
            ->method('save');

        $service = new BookQueueService($bookQueueRepositoryMock);

        $service->recountQueue($this->book);
    }

    public function testIncrementationOfMissedOpportunity(): void
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

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->book = new Book;
    }
}