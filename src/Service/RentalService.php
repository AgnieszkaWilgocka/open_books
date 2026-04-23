<?php

namespace App\Service;

use App\Entity\Rental;
use App\Entity\RentalToken;
use App\Entity\User;
use App\Repository\RentalRepository;
use DateTimeImmutable;

class RentalService
{
    public function __construct(private RentalRepository $rentalRepository) {}

    public function handleRentalCreation(Rental $rental, User $user): void
    {
        $this->finalizeRental($rental, $user);
    }

    public function createFromToken(RentalToken $token): Rental
    {
        $rental = new Rental();

        $rental->setCreatedAt(new DateTimeImmutable());
        $rental->setUpdatedAt(new DateTimeImmutable());
        $rental->setRentedAt(new DateTimeImmutable());
        $rental->setBook($token->getBook());
        $rental->setOwner($token->getUser());
        $rental->setDeadline(new DateTimeImmutable('+2 weeks'));

        $this->rentalRepository->save($rental);

        return $rental;
    }

    public function finalizeRental(Rental $rental, User $user): void
    {
        $rental->setCreatedAt(new DateTimeImmutable());
        $rental->setUpdatedAt(new DateTimeImmutable());
        $rental->setOwner($user);
        $rental->setRentedAt(new DateTimeImmutable());
        $rental->setDeadline(new DateTimeImmutable('+14 days'));

        $this->rentalRepository->save($rental);
    }

    public function markAsReturned(Rental $rental): void
    {
        $rental->setReturnedAt(new DateTimeImmutable());
        $rental->setUpdatedAt(new DateTimeImmutable());

        $this->rentalRepository->save($rental);
    }
}
