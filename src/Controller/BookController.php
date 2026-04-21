<?php
namespace App\Controller;

use App\Entity\Book;
use App\Entity\User;
use App\Form\Type\BookType;
use App\Repository\BookQueueRepository;
use App\Repository\BookRepository;
use App\Service\BookNotificationService;
use App\Service\FileUploaderHelper;
use App\Service\RentalTokenService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
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
    public function __construct(private BookRepository $bookRepository, private EntityManagerInterface $entityManager, private FileUploaderHelper $fileUploaderHelper, private BookNotificationService $bookNotification, private RentalTokenService $rentalTokenService, private BookQueueRepository $bookQueueRepository) {}

    #[Route('/', name: 'book_index', methods: ['GET'])]
    public function index(#[CurrentUser] ?User $user): Response
    {
        
        $this->rentalTokenService->clearExpiredRentalTokens();
        $books = $this->bookRepository->queryAll();
        $queuedBookIds = [];

        if ($user) {
            $queuedBooks = $this->bookQueueRepository->findBy([
                'user' => $user
            ]);

            $queuedBookIds = array_map(fn($bookQueue) => $bookQueue->getBook()->getId(), $queuedBooks);
        }
        
        return $this->render('/book/index.html.twig', [
            'books' => $books,
            'queuedBookIds' => $queuedBookIds
            ]);
    }

    #[Route('/create', name: 'book_create', methods: 'GET|POST')]
    public function create(Request $request): Response
    {
        $book = new Book();

        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // $category = $form->get('category')->getData();

            
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form->get('fileName')->getData();

            if ($uploadedFile) {
                $newFilename = $this->fileUploaderHelper->uploadFile($uploadedFile);
                
                $book->setImageFileName($newFilename);
            }

            $book->setCreatedAt(new DateTimeImmutable());
            $book->setUpdatedAt(new DateTimeImmutable());

            $this->entityManager->persist($book);
            $this->entityManager->flush();

            $this->bookNotification->sendNotification($book);

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

            $this->entityManager->persist($book);
            $this->entityManager->flush();

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
            $this->entityManager->remove($book);
            $this->entityManager->flush();

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