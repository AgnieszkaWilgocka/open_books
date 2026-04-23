<?php

namespace App\Service;

use App\Entity\Book;
use App\Entity\RentalToken;
use App\Entity\User;
use App\Repository\RentalTokenRepository;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\RequestStack;

class RentalTokenService
{
    public function __construct(private RentalTokenRepository $rentalTokenRepository, private RequestStack $requestStack) {}

    public function clearExpiredRentalTokens($expiredTokens): void
    {
        /** @var RentalToken $expiredToken */
        foreach ($expiredTokens as $expiredToken) {
            $this->rentalTokenRepository->delete($expiredToken);

            $this->requestStack->getSession()->getFlashBag()->add('warning', 'wypisano userow z kolejki');
        }
    }

    public function generateToken(Book $book, User $user): RentalToken
    {
        $rentalToken = new RentalToken();
        $rentalToken->setContent(bin2hex(random_bytes(32)));
        $rentalToken->setBook($book);
        $rentalToken->setUser($user);
        $rentalToken->setExpirationDate(new DateTimeImmutable('+24 hours'));

        $this->rentalTokenRepository->save($rentalToken);

        return $rentalToken;
    }

    public function consumeToken(RentalToken $token): void
    {
        $this->rentalTokenRepository->delete($token);
    }

    public function getExpiredTokens(): array
    {
        return $this->rentalTokenRepository->queryExpiredTokens();
    }
}
