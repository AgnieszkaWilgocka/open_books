<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Rental;
use App\Entity\RentalToken;
use App\Entity\User;
use App\Form\Type\RentalType;
use App\Form\Type\SearchRentalType;
use App\Repository\BookQueueRepository;
use App\Repository\BookRepository;
use App\Repository\RentalRepository;
use App\Security\Voter\RentalVoter;
use App\Service\RentalFlowService;
use DateTimeImmutable;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/rentals')]
class RentalController extends AbstractController
{
    public function __construct(private RentalRepository $rentalRepository, private BookRepository $bookRepository, private RentalFlowService $rentalFlowService, private BookQueueRepository $bookQueueRepository)
    {}

    #[Route('/', name: 'rental_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(Request $request, #[CurrentUser] User $user): Response
    {
        $form = $this->createForm(SearchRentalType::class, null, [
            'method' => 'GET'
        ]);

        $form->handleRequest($request);
        $data = $form->getData();

        $rentals = $this->rentalRepository->searchByParams($data['bookTitle'] ?? null, $data['writer'] ?? null, $data['deadline'] ?? null, $data['bookCategory'] ?? null, $user);
        $queues = $this->bookQueueRepository->queryAll($user);
        
        return $this->render('/rental/index.html.twig',
        [
            'rentals' => $rentals,
            'queues' => $queues,
            'form' => $form->createView()
        ]);
    }

    #[Route('/create/{id}', name: 'rental_create_with_book', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, #[CurrentUser] User $user, ?Book $book = null): Response
    {
        $rental = new Rental();
        $rental->setBook($book);

        $form = $this->createForm(RentalType::class, $rental);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $selectedBook = $form->get('book')->getData();

            if ($this->bookRepository->isCurrentlyRented($selectedBook)) {
                $this->addFlash('warning', 'This book is currently borrowed');

                return $this->redirectToRoute('book_index');
            }
            $this->rentalFlowService->createRental($rental, $user);
            $this->addFlash('success', 'Rental created successfully');

            return $this->redirectToRoute('rental_index');
        }

        return $this->render('/rental/create.html.twig',
        [
            'form' => $form,
            'book' => $book,
            'deadline' => new DateTimeImmutable('+14 days')
        ]);
    }

    #[Route('/create-by-token/{token}', name: 'create_with_token', requirements: ['token' => '[A-Za-z0-9]+'], methods: ['GET', 'POST'])]
    public function createWithToken(#[MapEntity(mapping: ['token' => 'content'])] ?RentalToken $token): Response
    {
        if (!$token) {
            $this->addFlash('warning', 'You dont have a permission!');
            
            return $this->redirectToRoute('book_index');
        }

        if ($token->getExpirationDate() <= new DateTimeImmutable()) {
            $this->addFlash('warning', 'This link expired!');

            return $this->redirectToRoute('book_index');
        }

        $this->rentalFlowService->createRentalFromToken($token);
        $this->addFlash('success', 'Rental created successfully');

        return $this->redirectToRoute('rental_index');
    }

    #[Route('/returnBook/{id}', name: 'rental_return', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'PUT'])]
    #[IsGranted(RentalVoter::RENTAL_RETURN, subject: 'rental')]
    public function returnBook(Request $request, Rental $rental): Response
    {
        if (!$rental->canBeReturned()) {
            $this->addFlash('warning', 'Rental already returned');
            return $this->redirectToRoute('rental_index');   
        }

        $form = $this->createForm(FormType::class, $rental, 
        [
            'action' => $this->generateUrl('rental_return', ['id' => $rental->getId()]),
            'method' => 'PUT'
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->rentalFlowService->returnRental($rental);
            $this->addFlash('success', 'Book returned successfully');

            return $this->redirectToRoute('rental_index');
        }

        return $this->render('/rental/rental_return.html.twig',
        [
            'form' => $form,
            'rental' => $rental,
            'book' => $rental->getBook()
        ]);
    }

    #[Route('/admin', name: 'rental_admin', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function admin(Request $request): Response
    {
        $form = $this->createForm(SearchRentalType::class, null, [
            'method' => 'GET'
        ]);

        $form->handleRequest($request);
        $data = $form->getData();

        $rentals = $this->rentalRepository->searchByParams($data['bookTitle'] ?? null, $data['writer'] ?? null, $data['deadline'] ?? null, $data['bookCategory'] ?? null);

        return $this->render('/rental/admin.html.twig', [
            'rentals' => $rentals,
            'form' => $form->createView()
        ]);
    }
}
