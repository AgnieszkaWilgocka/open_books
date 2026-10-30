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
    private Book $book;
    private Category $category;
    private FavoriteCategoryRepository $favoriteCategoryRepositoryMock;
    private MailerService $mailerServiceMock;
    private array $favoriteCategories;

    public function testDoesNotNotifyWhenFavoriteCategoriesIsEmpty(): void
    {
        $this->favoriteCategoryRepositoryMock
            ->expects($this->once())
            ->method('findBy')
            ->willReturn($this->favoriteCategories);

        $this->mailerServiceMock
            ->expects($this->never())
            ->method('sendNewBookInCategoryNotification');

        $bookNotificationService = new BookNotificationService($this->favoriteCategoryRepositoryMock, $this->mailerServiceMock);
        $bookNotificationService->sendNewBookNotification($this->book);
    }

    public function testDoesNotNotifyWhenNotificationsAreDisabled(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $favoriteCategory = new FavoriteCategory();
            $favoriteCategory->setNotificationsEnabled(false);
            $favoriteCategory->setCategory($this->category);

            $this->favoriteCategories[] = $favoriteCategory;
        }

        $this->favoriteCategoryRepositoryMock
            ->expects($this->once())
            ->method('findBy')
            ->with(['category' => $this->category])
            ->willReturn($this->favoriteCategories);

        $this->mailerServiceMock
            ->expects($this->never())
            ->method('sendNewBookInCategoryNotification');

        $bookNotificationService = new BookNotificationService($this->favoriteCategoryRepositoryMock, $this->mailerServiceMock);
        $bookNotificationService->sendNewBookNotification($this->book);
    }

    public function testNotifiesWhenFavoriteCategoryExistsAndNotificationsAreEnabled(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $favoriteCategory = new FavoriteCategory();
            $favoriteCategory->setNotificationsEnabled(true);
            $favoriteCategory->setCategory($this->category);

            $this->favoriteCategories[] = $favoriteCategory;
        }

        $this->favoriteCategoryRepositoryMock
            ->expects($this->once())
            ->method('findBy')
            ->with(['category' => $this->category])
            ->willReturn($this->favoriteCategories);

        $this->mailerServiceMock
            ->expects($this->exactly(count($this->favoriteCategories)))
            ->method('sendNewBookInCategoryNotification');

        $bookNotificationService = new BookNotificationService($this->favoriteCategoryRepositoryMock, $this->mailerServiceMock);
        $bookNotificationService->sendNewBookNotification($this->book);
    }

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->book = new Book();
        $this->category = new Category();
        $this->book->setCategory($this->category);
        $this->favoriteCategoryRepositoryMock = $this->createMock(FavoriteCategoryRepository::class);
        $this->mailerServiceMock = $this->createMock(MailerService::class);
        $this->favoriteCategories = [];
    }
}
