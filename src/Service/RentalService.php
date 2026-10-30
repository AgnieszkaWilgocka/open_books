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
        $now = new DateTimeImmutable();

        $rental->setCreatedAt($now);
        $rental->setUpdatedAt($now);
        $rental->setRentedAt($now);
        $rental->setBook($token->getBook());
        $rental->setOwner($token->getUser());
        $rental->setDeadline($now->modify('+14 days'));

        $this->rentalRepository->save($rental);

        return $rental;
    }

    public function finalizeRental(Rental $rental, User $user): void
    {
        $now = new DateTimeImmutable();
        $rental->setCreatedAt($now);
        $rental->setUpdatedAt($now);
        $rental->setOwner($user);
        $rental->setRentedAt($now);
        $rental->setDeadline($now->modify('+14 days'));

        $this->rentalRepository->save($rental);
    }

    public function markAsReturned(Rental $rental): void
    {
        $now = new DateTimeImmutable();
        $rental->setReturnedAt($now);
        $rental->setUpdatedAt($now);

        $this->rentalRepository->save($rental);
    }
}
