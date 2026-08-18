<?php

use App\Entity\Book;
use App\Entity\Category;
use App\Entity\FavoriteCategory;
use App\Repository\FavoriteCategoryRepository;
use App\Service\BookNotificationService;
use App\Service\MailerService;
use PHPUnit\Framework\TestCase;

class BookNotificationServiceTest extends TestCase
{
    
    public function testDoesNotNotifyWhenFavoriteCategoriesIsEmpty(): void
    {
        $book = new Book();
        $category = new Category();
        $book->setCategory($category);

        $favoriteCategoryRepositoryMock = $this->createMock(FavoriteCategoryRepository::class);

        $favoriteCategoryRepositoryMock
            ->expects($this->once())
            ->method('findBy')
            ->willReturn([]);

        $mailerServiceMock = $this->createMock(MailerService::class);
        $mailerServiceMock
            ->expects($this->never())
            ->method('sendNewBookInCategoryNotification');

        $bookNotificationService = new BookNotificationService($favoriteCategoryRepositoryMock, $mailerServiceMock);
        $bookNotificationService->sendNewBookNotification($book);
    }

    public function testDoesNotNotifyWhenNotificationsAreDisabled(): void
    {
        $book = new Book();
        $category = new Category();
        $book->setCategory($category);
        $favoriteCategories = [];

        for ($i = 0; $i < 3; $i++) {
            $favoriteCategory = new FavoriteCategory();
            $favoriteCategory->setNotificationsEnabled(false);
            $favoriteCategory->setCategory($category);

            $favoriteCategories[] = $favoriteCategory;
        }

        $favoriteCategoryRepositoryMock = $this->createMock(FavoriteCategoryRepository::class);

        $favoriteCategoryRepositoryMock
            ->expects($this->once())
            ->method('findBy')
            ->with(['category' => $category])
            ->willReturn($favoriteCategories);

        $mailerServiceMock = $this->createMock(MailerService::class);

        $mailerServiceMock
            ->expects($this->never())
            ->method('sendNewBookInCategoryNotification');

        $bookNotificationService = new BookNotificationService($favoriteCategoryRepositoryMock, $mailerServiceMock);
        $bookNotificationService->sendNewBookNotification($book);
    }

    public function testNotifiesWhenFavoriteCategoryExistsAndNotificationsAreEnabled(): void
    {
        $book = new Book();
        $category = new Category();
        $book->setCategory($category);
        $favoriteCategories = [];

        for ($i = 0; $i < 3; $i++) {
            $favoriteCategory = new FavoriteCategory();
            $favoriteCategory->setNotificationsEnabled(true);
            $favoriteCategory->setCategory($category);

            $favoriteCategories[] = $favoriteCategory;
        }

        $favoriteCategoryRepositoryMock = $this->createMock(FavoriteCategoryRepository::class);

        $favoriteCategoryRepositoryMock
            ->expects($this->once())
            ->method('findBy')
            ->with(['category' => $category])
            ->willReturn($favoriteCategories);

        $mailerServiceMock = $this->createMock(MailerService::class);

        $mailerServiceMock
            ->expects($this->exactly(count($favoriteCategories)))
            ->method('sendNewBookInCategoryNotification');

        $bookNotificationService = new BookNotificationService($favoriteCategoryRepositoryMock, $mailerServiceMock);
        $bookNotificationService->sendNewBookNotification($book);
    }
}