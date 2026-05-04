<?php

namespace App\Service;

use App\Entity\BookQueue;
use App\Entity\User;
use App\Repository\BookQueueRepository;

class BookService
{
    // public function __construct(private BookQueueRepository $bookQueueRepository) {}
    
    // public function prepareBookData(?User $user = null): array
    // {
    //     $queuedBooks = $this->bookQueueRepository->queryAll();
    //     $queuedUserBooks = [];
    //     $queuedUserBooksIds = [];
    //     $queuedBooksIds = [];


    //     if ($user) {
    //         $queuedUserBooks = $this->bookQueueRepository->findBy([
    //             'user' => $user
    //         ]);
    //     }

    //     $queuedUserBooksIds = array_map(fn(BookQueue $qbook) => $qbook->getBook()->getId(), $queuedUserBooks);
    //     $queuedBooksIds = array_map(fn(BookQueue $qbook) => $qbook->getBook()->getId(), $queuedBooks);

    //     return [
    //         'queuedUserBooksIds' => $queuedUserBooksIds,
    //         'queuedBooksIds' => $queuedBooksIds
    //     ];
    // }
}