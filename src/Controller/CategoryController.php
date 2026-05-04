<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\FavoriteCategory;
use App\Entity\User;
use App\Form\Type\CategoryType;
use App\Repository\BookRepository;
use App\Repository\CategoryRepository;
use App\Service\BookQueueService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/categories')]
class CategoryController extends AbstractController
{

    public function __construct(private EntityManagerInterface $entityManager, private CategoryRepository $categoryRepository, private BookRepository $bookRepository, private BookQueueService $bookQueueService) {}

    #[Route('/', name: 'category_index', methods: ['GET'])]
    public function index(#[CurrentUser] ?User $user = null) : Response
    {
        $categories = $this->categoryRepository->findAll();

        if ($user) {
            $userFavCategories = array_map(fn(FavoriteCategory $fc) => $fc->getCategory(), $user->getFavoriteCategories()->toArray());
            $userFavCategoryIds = array_map(fn(Category $category) => $category->getId(), $userFavCategories);
        } else {
            $userFavCategories = [];
            $userFavCategoryIds = [];
        }

        return $this->render(
            '/category/index.html.twig',
            [
                'categories' => $categories,
                'userFavCategories' => $userFavCategories,
                'userFavCategoryIds' => $userFavCategoryIds
            ]
        );
    }

    #[Route('/show/{id}', name: 'category_show', requirements: ['id' => '[1-9]\d*'], methods: ['GET'])]
    public function show(Category $category): Response
    {
        $categoryBooks = $this->bookRepository->findByCategory($category);
        // dd($categoryBooks);
        $queuedBooksData = $this->bookQueueService->prepareQueuedBooksData();
        
        // dd('he');
        return $this->render('/category/show.html.twig',
        [
            'category' => $category,
            'categoryBooks' => $categoryBooks,
            'queuedBooksIds' => $queuedBooksData['queuedBooksIds'],
            'queuedUserBooksIds' => $queuedBooksData['queuedUserBooksIds']
        ]);
    }

    #[Route('/create', name: 'category_create', methods: ['GET', 'POST'])]
    public function create(Request $request): Response
    {
        $category = new Category();

        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($category);
            $this->entityManager->flush();

            $this->addFlash('success', 'Category created successfully');

            return $this->redirectToRoute('category_index');
        }

        return $this->render('/category/create.html.twig',
        [
            'form' => $form
        ]);
    }

    #[Route('/edit/{id}', name: 'category_edit', requirements: ['id' => '[1-9]\d*'], methods: ['GET','PUT'])]
    public function edit(Request $request, Category $category): Response
    {
        $form = $this->createForm(CategoryType::class, $category, [
            'action' => $this->generateUrl('category_edit', ['id' => $category->getId()]),
            'method' => 'PUT'
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($category);
            $this->entityManager->flush();

            $this->addFlash('success', 'Category updated successfully');

            return $this->redirectToRoute('category_index');
        }

        return $this->render(
            '/category/edit.html.twig',
            [
                'form' => $form,
                'category' => $category
            ]
        );
    }

    #[Route('/delete/{id}', name: 'category_delete', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'DELETE'])]
    public function delete(Request $request, Category $category): Response
    {
        $form = $this->createForm(FormType::class, $category, [
            'action' => $this->generateUrl('category_delete', ['id' => $category->getId()]),
            'method' => 'DELETE'
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->remove($category);
            $this->entityManager->flush();

            $this->addFlash('success', 'Category deleted successfully');

            return $this->redirectToRoute('category_index');
        }

        return $this->render(
            '/category/delete.html.twig',
            [
                'form' => $form,
                'category' => $category
            ]
        );
    }
}