<?php

use App\Entity\Book;
use App\Entity\RentalToken;
use App\Entity\User;
use App\Repository\RentalTokenRepository;
use App\Service\RentalTokenService as ServiceRentalTokenService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RequestStack;

class RentalTokenServiceTest extends TestCase
{
    public function testIsTokenGenerated(): void
    {
        $rentalTokenRepositoryMock = $this->createMock(RentalTokenRepository::class);
        $requestStackStub = $this->createStub(RequestStack::class);

        $book = new Book();
        $user = new User();

        $rentalTokenRepositoryMock
            ->expects($this->once())
            ->method('save');

        $rentalTokenService = new ServiceRentalTokenService($rentalTokenRepositoryMock, $requestStackStub);

        $rentalToken = $rentalTokenService->generateToken($book, $user);

        $this->assertNotNull($rentalToken);
        $this->assertInstanceOf(RentalToken::class, $rentalToken);
        $this->assertNotNull($rentalToken->getContent());
        $this->assertNotNull($rentalToken->getBook());
        $this->assertNotNull($rentalToken->getUser());
        $this->assertSame($book, $rentalToken->getBook());
        $this->assertSame($user, $rentalToken->getUser());
        $this->assertNotNull($rentalToken->getExpirationDate());
        $this->assertInstanceOf(DateTimeImmutable::class, $rentalToken->getExpirationDate());
    }
}