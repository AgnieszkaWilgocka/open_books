<?php

use App\Entity\Book;
use App\Entity\BookQueue;
use App\Entity\RentalToken;
use App\Entity\User;
use App\Service\BookQueueService;
use App\Service\MailerService;
use App\Service\RentalFlowService;
use App\Service\RentalService;
use App\Service\RentalTokenService;
use PHPUnit\Framework\TestCase;

class RentalFlowTest extends TestCase
{
    public function testDoesNotNotifyWhenQueueIsEmpty(): void 
    {
        $rentalServiceStub = $this->createStub(RentalService::class);
        $bookQueueServiceMock = $this->createMock(BookQueueService::class);
        $rentalTokenServiceMock = $this->createMock(RentalTokenService::class);

        $mailerServiceMock = $this->createMock(MailerService::class);
        $book = new Book();

        $bookQueueServiceMock
            ->expects($this->once())
            ->method('getTheFirstPositionInQueue')
            ->with($book)
            ->willReturn(null);
        
        $rentalTokenServiceMock
            ->expects($this->never())
            ->method('generateToken');

        $mailerServiceMock
            ->expects($this->never())
            ->method('sendBookAvailableNotification');

        $rentalFlowService = new RentalFlowService($rentalServiceStub, $mailerServiceMock, $bookQueueServiceMock, $rentalTokenServiceMock);

        $rentalFlowService->notifyFirstInQueue($book);
    }

    public function testNotifiesFirstUserInQueue(): void
    {
        $rentalServiceStub = $this->createStub(RentalService::class);
        $bookQueueServiceMock = $this->createMock(BookQueueService::class);
        $rentalTokenServiceMock = $this->createMock(RentalTokenService::class);
        $mailerServiceMock = $this->createMock(MailerService::class);

        $book = new Book();
        $bookQueue = new BookQueue();
        $user = new User();

        $bookQueue->setBook($book);
        $bookQueue->setUser($user);
        
        $token = new RentalToken();
        $token->setUser($user);
        $token->setBook($book);

        $bookQueueServiceMock
            ->expects($this->once())
            ->method('getTheFirstPositionInQueue')
            ->with($book)
            ->willReturn($bookQueue);
        
         $rentalTokenServiceMock
            ->expects($this->once())
            ->method('generateToken')
            ->with($bookQueue->getBook(), $bookQueue->getUser())
            ->willReturn($token);

        $mailerServiceMock
            ->expects($this->once())
            ->method('sendBookAvailableNotification')
            ->with($token);

        $rentalFlowService = new RentalFlowService($rentalServiceStub, $mailerServiceMock, $bookQueueServiceMock, $rentalTokenServiceMock);

        $rentalFlowService->notifyFirstInQueue($book);
    }
}