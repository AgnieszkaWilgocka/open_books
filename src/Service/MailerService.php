<?php

namespace App\Service;

use App\Entity\Book;
use App\Entity\BookQueue;
use App\Entity\FavoriteCategory;
use App\Entity\Rental;
use App\Entity\RentalToken;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

class MailerService
{
    public function __construct(private MailerInterface $mailer) {}

    private const APP_EMAIL = 'openbooks@example.com';

    public function sendCreatedRental(Rental $rental): void
    {
        $user = $rental->getOwner();

        $this->sendEmail(
            $user->getEmail(),
            'Time for another adventure!',
            '/mailer/rental_create.html.twig',
            [
                'username' => $user->getEmail(),
                'deadline' => $rental->getDeadline(),
                'book' => $rental->getBook()->getTitle()
            ]
            );
    }

    public function sendBookAvailableNotification(RentalToken $token): void
    {
        $user = $token->getUser();

        $this->sendEmail(
            $user->getEmail(),
            'Your book is now available!',
            '/mailer/book_available.html.twig',
            [
                'username' => $user->getEmail(),
                'book' => $token->getBook()->getTitle(),
                'token' => $token->getContent()
            ]
        );
    }

    public function sendNewBookInCategoryNotification(FavoriteCategory $favoriteCategory, Book $book): void
    {
        $user = $favoriteCategory->getOwner();

        $this->sendEmail(
            $user->getEmail(),
            'Check this new book!',
            '/mailer/book_notification.html.twig',
            [
                'username' => $user->getEmail(),
                'book' => $book->getTitle()
            ]
        );
    }

    public function sendRemoveFromBookQueue(User $user, Book $book): void
    {
        $this->sendEmail(
            $user->getEmail(),
            'Removed from the queue',
            '/mailer/book_queue/remove_from_the_queue.html.twig',
            [
                'username' => $user->getEmail(),
                'book' => $book->getTitle()
            ]
        );
    }

    public function sendSignUpQueue(BookQueue $bookQueue)
    {
        $user = $bookQueue->getUser();

        $this->sendEmail(
            $user->getEmail(),
            'Sign up to the queue',
            '/mailer/book_queue/sign_up_queue.html.twig',
            [
                'username' => $user->getEmail(),
                'book' => $bookQueue->getBook()->getTitle(),
                'position' => $bookQueue->getPosition()
            ]
        );
    }

    private function sendEmail(string $to, string $subject, string $template, array $context): void
    {
        $email = (new TemplatedEmail())
        ->from(self::APP_EMAIL)
        ->to($to)
        ->subject($subject)
        ->htmlTemplate($template)
        ->context($context);

        $this->mailer->send($email);
    }
}
