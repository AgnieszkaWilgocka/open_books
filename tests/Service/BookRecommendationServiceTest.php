<?php

use App\Entity\Book;
use App\Entity\Category;
use App\Entity\FavoriteCategory;
use App\Entity\User;
use App\Repository\BookRepository;
use App\Repository\FavoriteCategoryRepository;
use App\Service\BookRecommendationService;
use PHPUnit\Framework\TestCase;

class BookRecommendationServiceTest extends TestCase
{
    public function testRecommendBookWhenUserHasNoFavoriteCategory() {
        $favoriteCategoryRepositoryStub = $this->createStub(FavoriteCategoryRepository::class);
        $bookRepositoryMock = $this->createMock(BookRepository::class);

        $user = new User();

        $favoriteCategoryRepositoryStub
            ->method('queryRandom')
            ->willReturn(null);
        
        $bookRepositoryMock
            ->expects($this->never())
            ->method('findRandomByCategory');   
            
        $bookRecommendationService = new BookRecommendationService($favoriteCategoryRepositoryStub, $bookRepositoryMock);
        $result = $bookRecommendationService->recommendBook($user);

        $this->assertNull($result);
    }

    public function testRecommenBookBasedOnUserFavoriteCategory() {
        $book = new Book();
        $category = new Category();
        $favoriteCategory = new FavoriteCategory();
        $favoriteCategory->setCategory($category);
        $user = new User();

        $favoriteCategoryRepositoryMock = $this->createMock(FavoriteCategoryRepository::class);
        $bookRepositoryMock = $this->createMock(BookRepository::class);

        $favoriteCategoryRepositoryMock
            ->expects($this->once())
            ->method('queryRandom')
            ->with($user)
            ->willReturn($favoriteCategory);

        $bookRepositoryMock
            ->expects($this->once())
            ->method('findRandomByCategory')
            ->with($category)
            ->willReturn($book);
        
        $bookRecommendationService = new BookRecommendationService($favoriteCategoryRepositoryMock, $bookRepositoryMock);

        $result = $bookRecommendationService->recommendBook($user);

        $this->assertSame($book, $result);
    }
}