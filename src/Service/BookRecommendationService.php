<?php

namespace App\Service;

use App\Entity\Book;
use App\Entity\User;
use App\Repository\BookRepository;
use App\Repository\FavoriteCategoryRepository;

class BookRecommendationService
{
    public function __construct(private FavoriteCategoryRepository $favoriteCategoryRepository, private BookRepository $bookRepository) {}
    public function recommendBook(User $user): ?Book
    {
        $randomFavCategory = $this->favoriteCategoryRepository->queryRandom($user);
        $randomBook = $this->bookRepository->findRandomByCategory($randomFavCategory->getCategory());

        return $randomBook;
    }
}