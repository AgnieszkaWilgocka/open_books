<?php

use App\Entity\Book;
use App\Entity\Rental;
use App\Entity\RentalToken;
use App\Entity\User;
use App\Repository\RentalRepository;
use App\Service\RentalService;
use PHPUnit\Framework\TestCase;

class RentalServiceTest extends TestCase
{

    public function testIsRentalCreatedFromToken(): void
    {
        $token = new RentalToken();
        $token->setBook(new Book());
        $token->setUser(new User());

        $rentalRepositoryMock = $this->createMock(RentalRepository::class);

        $rentalRepositoryMock
            ->expects($this->once())
            ->method('save');

        $rentalService = new RentalService($rentalRepositoryMock);

        $rental = $rentalService->createFromToken($token);

        $this->assertNotNull($rental->getCreatedAt());
        $this->assertNotNull($rental->getUpdatedAt());
        $this->assertNotNull($rental->getRentedAt());
        $this->assertNotNull($rental->getOwner());
        $this->assertSame($token->getUser(), $rental->getOwner());
        $this->assertSame($token->getBook(), $rental->getBook());
        $this->assertEquals($rental->getRentedAt()->modify('+14 days'), $rental->getDeadline());
    }

    public function testIsRentalFinalized(): void
    {
        $rental = new Rental();
        $user = new User();
        $rentalRepositoryMock = $this->createMock(RentalRepository::class);

        $rentalRepositoryMock
            ->expects($this->once())
            ->method('save')
            ->with($rental);

        $rentalService = new RentalService($rentalRepositoryMock);
        $rentalService->finalizeRental($rental, $user);

        $this->assertNotNull($rental->getCreatedAt());
        $this->assertNotNull($rental->getUpdatedAt());
        $this->assertNotNull($rental->getOwner());
        $this->assertNotNull($rental->getRentedAt());
        $this->assertNotNull($rental->getDeadline());
        $this->assertSame($user, $rental->getOwner());
        $this->assertEquals($rental->getRentedAt()->modify("+14 days"), $rental->getDeadline());
    }

    public function testIsRentalMarkedAsReturned(): void 
    {
        $rental = new Rental();

        $rentalRepositoryMock = $this->createMock(RentalRepository::class);
        $rentalRepositoryMock
            ->expects($this->once())
            ->method('save');

        $rentalService = new RentalService($rentalRepositoryMock);

        $rentalService->markAsReturned($rental);

        $this->assertNotNull($rental->getReturnedAt());
        $this->assertNotNull($rental->getUpdatedAt());
    }
}