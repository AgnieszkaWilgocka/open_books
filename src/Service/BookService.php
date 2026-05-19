<?php

namespace App\Service;

use App\Entity\Book;
use App\Entity\User;

class BookService
{
   public function prepareBookState(Book $book, array $queuedUserBooksIds, array $queuedBooksIds, User $user): array
   {
        if (!$user) {
            return [
                'status' => 'guest',
                'action' => 'login'
            ];
        }

        if (count($book->getActiveRentals())) {

            $userRental = $book->getActiveRentalByUser($user);

            if ($userRental) {
                return [
                    'status' => 'borrowed',
                    'action' => 'return',
                    'rentalId' => $userRental->getId()
                ];
            }

            if (in_array($book->getId(), $queuedUserBooksIds)) {
                return [
                    'status' => 'borrowed',
                    'action' => 'waiting'
                ];
            }

            return [
                'status' => 'borrowed',
                'action' => 'notify'
            ];

        } 

        if (in_array($book->getId(), $queuedBooksIds)) {
            if(in_array($book->getId(), $queuedUserBooksIds)) {
                return [
                    'status' => 'borrowed',
                    'action' => 'waiting'
                ];
            }

            return [
                'status' => 'borrowed',
                'action' => 'notify'
            ];
        }
        
        return [
            'status' => 'available',
            'action' => 'borrow'
        ];
   }
}