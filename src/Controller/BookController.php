<?php
namespace App\Controller;

use App\Entity\Book;
use App\Enum\BookStatusEnum;
use App\Form\Type\BookType;
use App\Repository\BookRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/books')]
class BookController extends AbstractController
{
    public function __construct(private BookRepository $bookRepository, private EntityManagerInterface $entityManager) {}

    #[Route('/', name: 'book_index', methods: 'GET')]
    public function index(): Response
    {
        $books = $this->bookRepository->findAll();

        return $this->render('/book/index.html.twig', ['books' => $books]);
    }

    #[Route('/create', name: 'book_create', methods: 'GET|POST')]
    public function create(Request $request): Response
    {
        $book = new Book();

        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $book->setCreatedAt(new DateTimeImmutable());
            $book->setUpdatedAt(new DateTimeImmutable());
            $book->setStatus(BookStatusEnum::Available);

            $this->entityManager->persist($book);
            $this->entityManager->flush();

            return $this->redirectToRoute('book_index');        
        }

        return $this->render('book/create.html.twig', [
                'form' => $form
            ]);
    }
}