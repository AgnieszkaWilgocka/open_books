<?php

namespace App\Service;

use App\Entity\Book;
use App\Entity\User;

class BookQueueFlowService
{
    public function __construct(private BookQueueService $bookQueueService, private MailerService $mailerService) {}

    public function joinToQueue(Book $book, User $user): void
    {
        $queuedBook = $this->bookQueueService->createQueuedBook($book, $user);
        $this->mailerService->sendSignUpQueue($queuedBook);
    }
}