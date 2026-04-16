<?php

namespace App\Service;

use App\Entity\Book;
use App\Repository\FavoriteCategoryRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class BookNotificationService
{

    public function __construct(private FavoriteCategoryRepository $favoriteCategoryRepository, private MailerInterface $mailer) {}

    public function sendNotification(Book $book): void
    {
        $category = $book->getCategory();

        $favoriteCategories = $this->favoriteCategoryRepository->findBy([
            'category' => $category
        ]);

        if (empty($favoriteCategories)) return;

        foreach ($favoriteCategories as $favorite) {
            if ($favorite->isNotificationsEnabled()) {
                $user = $favorite->getOwner()->getEmail();
                $email = (new TemplatedEmail())
                    ->from('openbooks@example.com')
                    ->to($user)
                    ->subject('Check this new book')
                    ->htmlTemplate('/mailer/book_notification.html.twig')
                    ->context([
                        'username' => $user,
                        'book' => $book->getTitle()
                    ]);

                $this->mailer->send($email);
            }
        }
    }
}