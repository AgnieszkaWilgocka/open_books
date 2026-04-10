<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\Type\CategoryType;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/categories')]
class CategoryController extends AbstractController
{

    public function __construct(private EntityManagerInterface $entityManager, private CategoryRepository $categoryRepository) {}

    #[Route('/', name: 'category_index', methods: ['GET'])]
    public function index() : Response
    {
        $categories = $this->categoryRepository->findAll();

        return $this->render(
            '/category/index.html.twig',
            [
                'categories' => $categories
            ]
        );
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