<?php

namespace App\Controller;

use App\Repository\BookRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainPageController extends AbstractController
{
    public function __construct(private BookRepository $bookRepository, private CategoryRepository $categoryRepository) {}

    #[Route('/', name: 'app_home')]
    public function index(): Response
    {
        $popularBooks = $this->bookRepository->queryMostRented(3);
        $categories = $this->categoryRepository->queryByLimit(8);

        return $this->render('main/index.html.twig', [
            'popularBooks' => $popularBooks,
            'categories' => $categories
        ]);
    }
}