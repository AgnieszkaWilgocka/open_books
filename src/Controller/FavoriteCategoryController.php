<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\FavoriteCategory;
use App\Entity\User;
use App\Repository\CategoryRepository;
use App\Repository\FavoriteCategoryRepository;
use App\Service\BookRecommendationService;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/favorite')]
#[IsGranted('ROLE_USER')]
class FavoriteCategoryController extends AbstractController
{
    public function __construct(private FavoriteCategoryRepository $favoriteCategoryRepository, private BookRecommendationService $bookRecommendationService, private CategoryRepository $categoryRepository) {}

    #[Route('/', name: 'favorite_index')]
    public function index(#[CurrentUser] User $user): Response
    {
        $userFavCategoryIds = [];
        $categories = [];
        $favCategories = [];

        /** @var User $user */
        // if ($this->isGranted('ROLE_ADMIN')) {
            // $favCategories = $this->favoriteCategoryRepository->findAll();
        // } else {
        $favCategories = $this->favoriteCategoryRepository->queryAll($user);
        // }

        $userFavCategories = array_map(fn(FavoriteCategory $fc) => $fc->getCategory(), $user->getFavoriteCategories()->toArray());
        $userFavCategoryIds = array_map(fn(Category $category) => $category->getId(), $userFavCategories);
        $categories = $this->categoryRepository->findAll();


        $recommendedBook = $this->bookRecommendationService->recommendBook($user);
        
        return $this->render('favorite_category/index.html.twig', [
            'favCategories' => $favCategories,
            'recommendedBook' => $recommendedBook ?? null,
            'userFavCategoryIds' => $userFavCategoryIds,
            'categories' => $categories
            ]
        );
    }

    #[Route('/category/{id}/add', name: 'favorite_add', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'POST'])]
    public function add(Category $category, #[CurrentUser] $user): Response
    {
        $existingFav = $this->favoriteCategoryRepository->findOneBy([
            'owner' => $user,
            'category' => $category
        ]);

        if ($existingFav) {
            $this->addFlash('warning', 'You already have that category in your favorites!');

            return $this->redirectToRoute('category_index');
        }
        
        $favCategory = new FavoriteCategory();

        $favCategory->setNotificationsEnabled('true');
        $favCategory->setCategory($category);
        $favCategory->setOwner($user);
        $favCategory->setCreatedAt(new DateTimeImmutable());
        $favCategory->setUpdatedAt(new DateTimeImmutable());

        $this->favoriteCategoryRepository->save($favCategory);
        $this->addFlash('success', 'You added this category to your fav!');

        return $this->redirectToRoute('category_index');
    }

    #[Route('/category/{id}/delete', name:'favorite_delete', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'POST'])]
    public function delete(Category $category, #[CurrentUser] $user): Response
    {
        $favoriteCategory = $this->favoriteCategoryRepository->findOneBy([
            'owner' => $user,
            'category' => $category
        ]);

        $this->favoriteCategoryRepository->delete($favoriteCategory);
        $this->addFlash('success', 'You removed this category from your fav!');

        return $this->redirectToRoute('category_index');
    }

    #[Route('/category/set-notification-for/{id}', name: 'toggle_notifications', requirements: ['id' => '[1-9]\d*'], methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function toggleNotification(Request $request, FavoriteCategory $favoriteCategory): Response
    {
        $isNotificationEnabled = $request->request->has('notifications');

        $favoriteCategory->setNotificationsEnabled($isNotificationEnabled);
        $favoriteCategory->setUpdatedAt(new DateTimeImmutable());

        $this->favoriteCategoryRepository->save($favoriteCategory);

        return $this->redirectToRoute('favorite_index');
    }
}
