<?php

namespace App\Controller;

use App\Entity\Category;
use App\Entity\FavoriteCategory;
use App\Entity\User;
use App\Form\Type\CategoryType;
use App\Form\Type\SearchCategoryType;
use App\Repository\BookRepository;
use App\Repository\CategoryRepository;
use App\Service\BookQueueService;
use App\Service\BookService;
use Doctrine\ORM\EntityManagerInterface;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/categories')]
class CategoryController extends AbstractController
{

    public function __construct(private EntityManagerInterface $entityManager, private CategoryRepository $categoryRepository, private BookRepository $bookRepository, private BookQueueService $bookQueueService, private BookService $bookService) {}

    #[Route('/', name: 'category_index', methods: ['GET'])]
    public function index(Request $request, #[CurrentUser] ?User $user = null) : Response
    {

        $form = $this->createForm(SearchCategoryType::class, null, [
            'method' => 'GET'
        ]);
        
        $form->handleRequest($request);
        $data = $form->getData();

        $queryBuilder = $this->categoryRepository->searchByParams($data['title'] ?? null);

        $pagerfanta = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pagerfanta->setMaxPerPage(6);
        $pagerfanta->setCurrentPage($request->query->get('page', 1));

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
                'pager' => $pagerfanta,
                'form' => $form->createView(),
                'userFavCategories' => $userFavCategories,
                'userFavCategoryIds' => $userFavCategoryIds
            ]
        );
    }

    #[Route('/show/{id}', name: 'category_show', requirements: ['id' => '[1-9]\d*'], methods: ['GET'])]
    public function show(Category $category, #[CurrentUser] ?User $user): Response
    {
        $categoryBooks = $this->bookRepository->findByCategory($category);
        $queuedBooksData = $this->bookQueueService->prepareQueuedBooksData();

        $bookStates = [];

        foreach($categoryBooks as $book) {
            $bookStates[$book->getId()] = $this->bookService->prepareBookState($book, $queuedBooksData['queuedUserBooksIds'], $queuedBooksData['queuedBooksIds'], $user);
        }

        return $this->render('/category/show.html.twig',
        [
            'category' => $category,
            'categoryBooks' => $categoryBooks,
            'bookStates' => $bookStates
        ]);
    }

    #[Route('/create', name: 'category_create', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_ADMIN')]
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
    #[IsGranted('ROLE_ADMIN')]
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
    #[IsGranted('ROLE_ADMIN')]
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
