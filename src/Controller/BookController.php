<?php
namespace App\Controller;

use App\Entity\Book;
use App\Entity\BookQueue;
use App\Entity\User;
use App\Form\Type\BookType;
use App\Repository\BookQueueRepository;
use App\Repository\BookRepository;
use App\Service\BookNotificationService;
use App\Service\FileUploaderHelper;
use App\Service\RentalFlowService;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/books')]
class BookController extends AbstractController
{
    public function __construct(private BookRepository $bookRepository, private FileUploaderHelper $fileUploaderHelper, private BookNotificationService $bookNotification, private BookQueueRepository $bookQueueRepository, private RentalFlowService $rentalFlowService) {}

    #[Route('/', name: 'book_index', methods: ['GET'])]
    public function index(#[CurrentUser] ?User $user): Response
    {
        $this->rentalFlowService->handleClearedTokens();
        $books = $this->bookRepository->queryAll();
        
        $popularBooks = $this->bookRepository->countRentalsForBook();

        $queuedBooks = $this->bookQueueRepository->queryAll();
        $queuedUserBooksIds = [];

        if ($user) {
            $queuedUserBooksIds = array_filter($queuedBooks, fn(BookQueue $qbook) => $qbook->getUser());
        }
        
        $queuedBooksIds = [];
        if (!empty($queuedBooks)) {
            $queuedBooksIds = array_map(fn(BookQueue $qbook) => $qbook->getBook()->getId(), $queuedBooks);
        }
        
        return $this->render('/book/index.html.twig', [
            
            'books' => $books,
            'popularBooks' => $popularBooks,
            'queuedBooksIds' => $queuedBooksIds,
            'queuedUserBooksIds' => $queuedUserBooksIds
            ]);
    }

    #[Route('/create', name: 'book_create', methods: 'GET|POST')]
    public function create(Request $request): Response
    {
        $book = new Book();

        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form->get('fileName')->getData();

            if ($uploadedFile) {
                $newFilename = $this->fileUploaderHelper->uploadFile($uploadedFile);
                
                $book->setImageFileName($newFilename);
            }

            $book->setCreatedAt(new DateTimeImmutable());
            $book->setUpdatedAt(new DateTimeImmutable());

            $this->bookRepository->save($book);
            $this->bookNotification->sendNewBookNotification($book);

            $this->addFlash('success', 'Book created successfully');

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
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form->get('fileName')->getData();

            if ($uploadedFile) {
                $newFilename = $this->fileUploaderHelper->uploadFile($uploadedFile);
                
                $book->setImageFileName($newFilename);
            }
            
            $book->setUpdatedAt(new DateTimeImmutable());

            $this->bookRepository->save($book); 
            $this->addFlash('success', 'Book updated successfully');

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
            $this->bookRepository->delete($book);
            $this->addFlash('success', 'Book deleted successfully');

            return $this->redirectToRoute('book_index');
        }

        return $this->render('/book/delete.html.twig', 
        [
            'form' => $form,
            'book' => $book,
        ]);
    }
}
