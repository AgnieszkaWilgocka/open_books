<?php
namespace App\Controller;

use App\Entity\Book;
use App\Form\Type\BookType;
use App\Repository\BookRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/books')]
class BookController extends AbstractController
{
    public function __construct(private BookRepository $bookRepository, private EntityManagerInterface $entityManager) {}

    #[Route('/', name: 'book_index', methods: ['GET'])]
    public function index(): Response
    {
        $books = $this->bookRepository->getBooks();

        // var_dump($books);

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

            $this->entityManager->persist($book);
            $this->entityManager->flush();

            return $this->redirectToRoute('book_index');        
        }

        return $this->render('book/create.html.twig', [
                'form' => $form
            ]);
    }

    #[Route('/edit/{id}', name: 'book_edit', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'PUT'])]
    public function edit(Request $request, Book $book): Response 
    {
        $form = $this->createForm(BookType::class, $book,
        [
            'action' => $this->generateUrl('book_edit', ['id' => $book->getId()]),
            'method' => 'PUT',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $book->setUpdatedAt(new DateTimeImmutable());

            $this->entityManager->persist($book);
            $this->entityManager->flush();

            return $this->redirectToRoute('book_index');
        }

        return $this->render('book/edit.html.twig', [
            'form' => $form,
            'book' => $book,
        ]);
    } 

    #[Route('/delete/{id}', name: 'book_delete', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'DELETE'])]
    public function delete(Request $request, Book $book): Response
    {
        $form = $this->createForm(FormType::class, $book,
        [
            'action' => $this->generateUrl('book_delete', ['id' => $book->getId()]),
            'method' => 'DELETE',
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() &&  $form->isValid()) {
            $this->entityManager->remove($book);
            $this->entityManager->flush();

            return $this->redirectToRoute('book_index');
        }

        return $this->render('/book/delete.html.twig', 
        [
            'form' => $form,
            'book' => $book,
        ]);
    }
}