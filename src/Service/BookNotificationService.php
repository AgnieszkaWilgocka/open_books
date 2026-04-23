<?php

namespace App\Service;

use App\Entity\Book;
use App\Repository\FavoriteCategoryRepository;

class BookNotificationService
{

    public function __construct(private FavoriteCategoryRepository $favoriteCategoryRepository, private MailerService $mailerService) {}

    public function sendNewBookNotification(Book $book): void
    {
        $category = $book->getCategory();

        $favoriteCategories = $this->favoriteCategoryRepository->findBy([
            'category' => $category
        ]);

        if (empty($favoriteCategories)) return;

        foreach ($favoriteCategories as $favorite) {
            if ($favorite->isNotificationsEnabled()) {
                $this->mailerService->sendNewBookInCategoryNotification($favorite, $book);
            }
        }
    }
}