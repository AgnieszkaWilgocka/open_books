<?php

namespace App\Service;

use App\Entity\Book;
use App\Entity\RentalToken;
use App\Entity\User;
use App\Repository\BookQueueRepository;
use App\Repository\RentalTokenRepository;
use DateTimeImmutable;
use Symfony\Component\HttpFoundation\RequestStack;

class RentalTokenService
{
    public function __construct(private RentalTokenRepository $rentalTokenRepository, private BookQueueRepository $bookQueueRepository, private RequestStack $requestStack, private MailerService $mailerService) {}

    public function clearExpiredRentalTokens(): void
    {
        $expiredTokens = $this->rentalTokenRepository->queryExpiredTokens();
        if (count($expiredTokens) == 0) return;

        /** @var RentalToken $expiredToken */
        foreach ($expiredTokens as $expiredToken) {
            $missing_queue = $this->bookQueueRepository->findOneBy([
                'user' => $expiredToken->getUser(),
                'book' => $expiredToken->getBook()
            ]);

            $missing_opportunity = $missing_queue->getMissingOpportunity();
            $missing_queue->setMissingOpportunity($missing_opportunity + 1);

            if ($missing_queue->getMissingOpportunity() >= 3) {
                $this->bookQueueRepository->delete($missing_queue);

                // querying new queue for this book 
                $newQueueList = $this->bookQueueRepository->findBy(
                    [
                        'book' => $expiredToken->getBook()
                    ]            
                );

                foreach ($newQueueList as $queue) {
                    $newPosition = $queue->setPosition($queue->getPosition() - 1);
                    if ($newPosition === 1) {
                        $freshToken = $this->generateToken($expiredToken->getBook(), $expiredToken->getUser());
                        $this->mailerService->sendBookAvailableNotification($freshToken);
                    }

                    $this->bookQueueRepository->save($queue);
                }

                $this->requestStack->getSession()->getFlashBag()->add('warning', 'wypisano userow z kolejki');
                // $this->mailerService->sendRemoveFromBookQueue($expiredToken->getUser(), $expiredToken->getBook());
            } 

            $this->rentalTokenRepository->delete($expiredToken);
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
}
