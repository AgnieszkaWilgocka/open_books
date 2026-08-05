<?php

use App\Entity\Book;
use App\Entity\Rental;
use App\Entity\User;
use App\Service\BookService;
use PHPUnit\Framework\TestCase;

class BookServiceTest extends TestCase
{

    public function testReturnsGuestStateWhenUserIsNull()
    {
        //Arrange
        $bookStub = $this->createStub(Book::class);
        $service = new BookService();

        //Act
        $result = $service->prepareBookState($bookStub, [], [], null);

        //Assert
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
        
        $service = new BookService();

        $result = $service->prepareBookState($bookStub, [], [], $user);

        $this->assertSame([
                    'status' => 'borrowed',
                    'action' => 'return',
                    'rentalId' => 123], $result);
    }

    public function testReturnsWaitingActionWhenUserIsQueuedForBorrowedBook()
    {
        $bookStub = $this->createStub(Book::class);
        $rental = new Rental();
        $user = new User();
        
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

        $service = new BookService();

        $result = $service->prepareBookState($bookStub, $queuedUserBooksIds, [], $user);

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
        $user = new User();

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

        $service = new BookService();

        $result = $service->prepareBookState($bookStub, $queuedUserBooksIds, [], $user);

        $this->assertSame([
            'status' => 'borrowed',
            'action' => 'notify'
        ], $result);
    }

    public function testReturnsWaitingActionWhenBookIsReservedForCurrentUser()
    {
        $user = new User();
        $bookStub = $this->createStub(Book::class);
        $bookStub
            ->method('getActiveRentals')
            ->willReturn([]);

        $queuedUserBooksIds = [1];
        $queuedBooksIds = [1];

        $bookStub
            ->method('getId')
            ->willReturn(1);

        $service = new BookService();
        
        $result = $service->prepareBookState($bookStub, $queuedUserBooksIds, $queuedBooksIds, $user);

        $this->assertSame([
            'status' => 'borrowed',
            'action' => 'waiting'
        ], $result);
    }

    public function testReturnsNotifyActionWhenBookIsReservedForOtherUsers()
    {
        $user = new User();
        $bookStub = $this->createStub(Book::class);
        $bookStub
            ->method('getActiveRentals')
            ->willReturn([]);

        $bookStub
            ->method('getId')
            ->willReturn(2);

        $queuedBooksIds = [2];
        $queuedUserBooksIds = [];

        $service = new BookService;

        $result = $service->prepareBookState($bookStub, $queuedUserBooksIds, $queuedBooksIds, $user);

        $this->assertSame([
            'status' => 'borrowed',
            'action' => 'notify'
        ], $result);
    }

    public function testReturnsBorrowActionWhenBookIsAvailable()
    {
        $user = new User();

        $bookStub = $this->createStub(Book::class);

        $bookStub
            ->method('getActiveRentals')
            ->willReturn([]);

        $queuedBooksIds = [];
        $queuedUserBooksIds = [];

        $service = new BookService();

        $result = $service->prepareBookState($bookStub, $queuedUserBooksIds, $queuedBooksIds, $user);

        $this->assertSame([
            'status' => 'available',
            'action' => 'borrow'
        ], $result);
    }
}