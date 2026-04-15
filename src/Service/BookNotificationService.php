<?php

namespace App\Service;

use App\Entity\Book;
use App\Entity\Category;
use App\Entity\FavoriteCategory;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class BookNotificationService
{

    public function __construct(private MailerInterface $mailer, private UserRepository $userRepository) {}

    public function sendNotification(Category $category, Book $book): void
    {
        $users = $this->prepareUserList($category);

        // todo - check if users > 0
        
        foreach ($users as $user) {
            $email = (new TemplatedEmail())
            ->from('openbooks@example.com')
            ->to($user->getEmail())
            ->subject('Check this fresh book')
            ->htmlTemplate('mailer/book_notification.html.twig')
            ->context([
                'username' => $user->getEmail(),
                'book' => $book->getTitle()
            ]);

            $this->mailer->send($email);
        }
    }

    private function prepareUserList(Category $category): array
    {
        $usersToNotify = [];

        $users = $this->userRepository->findAll();

            /** @var User $user */
            foreach ($users as $user) {
                $favCategories = $user->getFavoriteCategories();
                /** @var FavoriteCategory $favorite */
                foreach ($favCategories as $favorite) {
                    if ($favorite->getCategory() === $category && $favorite->isNotificationsEnabled()) {
                        $usersToNotify[] = $user;
                    }
                }
            }

        return $usersToNotify;
    }
}