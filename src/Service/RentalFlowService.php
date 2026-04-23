<?php

namespace App\Service;

use App\Entity\Book;
use App\Entity\Rental;
use App\Entity\RentalToken;
use App\Entity\User;

class RentalFlowService
{
    public CONST MISSING_LIMIT = 3;

    public function __construct(private RentalService $rentalService, private MailerService $mailerService, private BookQueueService $bookQueueService, private RentalTokenService $rentalTokenService) {}

    public function createRentalFromToken(RentalToken $token): void
    {
        $rental = $this->rentalService->createFromToken($token);
        $this->mailerService->sendCreatedRental($rental);
        $this->rentalTokenService->consumeToken($token);
        $this->bookQueueService->deletePositionFromQueue($token->getBook(), $token->getUser());
        $this->bookQueueService->recountQueue($rental->getBook());
    }

    public function createRental(Rental $rental, User $user): void
    {
        $this->rentalService->handleRentalCreation($rental, $user);
        $this->mailerService->sendCreatedRental($rental);
    }

    public function returnRental(Rental $rental): void
    {
        $this->rentalService->markAsReturned($rental);

        $this->notifyFirstInQueue($rental->getBook());
    }

    public function handleClearedTokens(): void
    {
        $expiredTokens = $this->rentalTokenService->getExpiredTokens();

        /** @var RentalToken $expiredToken */
        foreach ($expiredTokens as $expiredToken) {
            $queuedBook = $this->bookQueueService->queuedBookWithExpiredToken($expiredToken);

            if (!$queuedBook) {
                continue;
            }

            $this->bookQueueService->increaseMissingOpportunity($queuedBook);

            if ($queuedBook->getMissingOpportunity() < self::MISSING_LIMIT ) {
                $token = $this->rentalTokenService->generateToken($queuedBook->getBook(), $queuedBook->getUser());
                // $this->mailerService->sendBookAvailableNotification($token);

                continue;
            } 

            $this->bookQueueService->deleteBookQueueWithLimitExceeded($queuedBook);
            $this->bookQueueService->recountQueue($queuedBook->getBook());

            $this->notifyFirstInQueue($expiredToken->getBook());
        }

        $this->rentalTokenService->clearExpiredRentalTokens($expiredTokens);
    }

    public function notifyFirstInQueue(Book $book): void
    {
        $firstPosition = $this->bookQueueService->getTheFirstPositionInQueue($book);

        if ($firstPosition) {
            $token = $this->rentalTokenService->generateToken($firstPosition->getBook(), $firstPosition->getUser());
            $this->mailerService->sendBookAvailableNotification($token);
        }
    }
}
