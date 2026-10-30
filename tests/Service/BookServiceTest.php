<?php

use App\Entity\Book;
use App\Entity\Rental;
use App\Entity\User;
use App\Service\BookService;
use PHPUnit\Framework\TestCase;

class BookServiceTest extends TestCase
{
    private BookService $bookService;
    private User $user;

    public function testReturnsGuestStateWhenUserIsNull()
    {
        $bookStub = $this->createStub(Book::class);

        $result = $this->bookService->prepareBookState($bookStub, [], [], null);

        $this->assertSame(['status' => 'guest', 'action' => 'login'], $result);
    }

    public function testReturnsReturnActionWhenUserCurrentlyBorrowing()
    {
        $bookStub = $this->createStub(Book::class);
        $rentalStub = $this->createStub(Rental::class);
        $user = new User();

        $rentalStub
            ->method('getId')
            ->willReturn(123);

        $bookStub
            ->method('getActiveRentalByUser')
            ->willReturn($rentalStub);
        
        $bookStub
            ->method('getActiveRentals')
            ->willReturn([$rentalStub]);

        $result = $this->bookService->prepareBookState($bookStub, [], [], $user);

        $this->assertSame([
                    'status' => 'borrowed',
                    'action' => 'return',
                    'rentalId' => 123], $result);
    }

    public function testReturnsWaitingActionWhenUserIsQueuedForBorrowedBook()
    {
        $bookStub = $this->createStub(Book::class);
        $rental = new Rental();
        
        $bookStub 
            ->method('getId')
            ->willReturn(1);

        $bookStub
            ->method('getActiveRentals')
            ->willReturn([$rental]);

        $bookStub
            ->method('getActiveRentalByUser')
            ->willReturn(null);

        $queuedUserBooksIds = [1, 2, 3];

        $result = $this->bookService->prepareBookState($bookStub, $queuedUserBooksIds, [], $this->user);

        $this->assertSame([
                    'status' => 'borrowed',
                    'action' => 'waiting'
                    ],
                    $result);
    }

    public function testReturnsNotifyActionWhenUserCanEnrollToWaitingList()
    {
        $bookStub = $this->createStub(Book::class);
        $rental = new Rental();

        $bookStub
            ->method('getId')
            ->willReturn(5);
        
        $bookStub
            ->method('getActiveRentals')
            ->willReturn([$rental]);

        $bookStub
            ->method('getActiveRentalByUser')
            ->willReturn(null);

        $queuedUserBooksIds = [1, 2, 3];

        $result = $this->bookService->prepareBookState($bookStub, $queuedUserBooksIds, [], $this->user);

        $this->assertSame([
            'status' => 'borrowed',
            'action' => 'notify'
        ], $result);
    }

    public function testReturnsWaitingActionWhenBookIsReservedForCurrentUser()
    {
        $bookStub = $this->createStub(Book::class);
        $bookStub
            ->method('getActiveRentals')
            ->willReturn([]);

        $queuedUserBooksIds = [1];
        $queuedBooksIds = [1];

        $bookStub
            ->method('getId')
            ->willReturn(1);
        
        $result = $this->bookService->prepareBookState($bookStub, $queuedUserBooksIds, $queuedBooksIds, $this->user);

        $this->assertSame([
            'status' => 'borrowed',
            'action' => 'waiting'
        ], $result);
    }

    public function testReturnsNotifyActionWhenBookIsReservedForOtherUsers()
    {
        $bookStub = $this->createStub(Book::class);
        $bookStub
            ->method('getActiveRentals')
            ->willReturn([]);

        $bookStub
            ->method('getId')
            ->willReturn(2);

        $queuedBooksIds = [2];
        $queuedUserBooksIds = [];

        $result = $this->bookService->prepareBookState($bookStub, $queuedUserBooksIds, $queuedBooksIds, $this->user);

        $this->assertSame([
            'status' => 'borrowed',
            'action' => 'notify'
        ], $result);
    }

    public function testReturnsBorrowActionWhenBookIsAvailable()
    {
        $bookStub = $this->createStub(Book::class);

        $bookStub
            ->method('getActiveRentals')
            ->willReturn([]);

        $queuedBooksIds = [];
        $queuedUserBooksIds = [];

        $result = $this->bookService->prepareBookState($bookStub, $queuedUserBooksIds, $queuedBooksIds, $this->user);

        $this->assertSame([
            'status' => 'available',
            'action' => 'borrow'
        ], $result);
    }

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->bookService = new BookService();
        $this->user = new User();
    }
}