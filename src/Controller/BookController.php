<?php
namespace App\Controller;

use App\Entity\Book;
use App\Entity\User;
use App\Form\Type\BookType;
use App\Form\Type\SearchBookType;
use App\Repository\BookRepository;
use App\Service\BookNotificationService;
use App\Service\BookQueueService;
use App\Service\BookService;
use App\Service\FileUploaderHelper;
use DateTimeImmutable;
use Pagerfanta\Doctrine\ORM\QueryAdapter;
use Pagerfanta\Pagerfanta;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/books')]
class BookController extends AbstractController
{
    public function __construct(private BookRepository $bookRepository, private FileUploaderHelper $fileUploaderHelper, private BookNotificationService $bookNotification, private BookQueueService $bookQueueService, private BookService $bookService) {}

    #[Route('/', name: 'book_index', methods: ['GET'])]
    public function index(Request $request, #[CurrentUser] ?User $user = null): Response
    {
        // $this->rentalFlowService->handleClearedTokens();
        $form = $this->createForm(SearchBookType::class, null, [
            'method' => 'GET',
        ]);

        $form->handleRequest($request);
        $data = $form->getData();

        $queryBuilder = $this->bookRepository->searchByParams($data['title'] ?? null, $data['year'] ?? null, $data['category'] ?? null);
        
        $pagerfanta = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pagerfanta->setMaxPerPage(9);
        $pagerfanta->setCurrentPage($request->query->get('page', 1));
        
        $popularBooks = $this->bookRepository->queryMostRented(2);
        $queuedBooksData = $this->bookQueueService->prepareQueuedBooksData($user);

        $bookStates = [];

        foreach($pagerfanta->getCurrentPageResults() as $book) {
            $bookStates[$book->getId()] = $this->bookService->prepareBookState($book, $queuedBooksData['queuedUserBooksIds'], $queuedBooksData['queuedBooksIds'], $user);
        }

        return $this->render('/book/index.html.twig', [
            'pager' => $pagerfanta,
            'form' => $form->createView(),
            'popularBooks' => $popularBooks,
            'bookStates' => $bookStates
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

    #[Route('/admin', name: 'book_admin', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function admin(Request $request): Response
    {
        $queryBuilder = $this->bookRepository->queryAllQueryBuilder();

        $pagerfanta = new Pagerfanta(new QueryAdapter($queryBuilder));
        $pagerfanta->setMaxPerPage(8);
        $pagerfanta->setCurrentPage($request->query->get('page', 1));

        return $this->render('/book/admin.html.twig', [
            'pager' => $pagerfanta
        ]);
    }
}
