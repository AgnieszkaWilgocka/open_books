<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\BookQueue;
use App\Entity\Rental;
use App\Entity\RentalToken;
use App\Entity\User;
use App\Form\Type\RentalType;
use App\Repository\BookQueueRepository;
use App\Repository\BookRepository;
use App\Repository\RentalRepository;
use App\Repository\RentalTokenRepository;
use App\Security\Voter\RentalVoter;
use App\Service\MailerService;
use App\Service\RentalTokenService;
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
    public function __construct(private RentalRepository $rentalRepository, private BookRepository $bookRepository, private BookQueueRepository $bookQueueRepository, private RentalTokenService $rentalTokenService, private RentalTokenRepository $rentalTokenRepository, private MailerService $mailerService)
    {}

    #[Route('/', name: 'rental_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(#[CurrentUser] User $user): Response
    {
        $rentals = $this->rentalRepository->findAll();
        // $rentals = $this->rentalRepository->queryAll($user);


        return $this->render('/rental/index.html.twig',
        [
            'rentals' => $rentals
        ]);
    }

    #[Route('/create', name: 'rental_create', methods: ['GET', 'POST'])]
    #[Route('/create/{id}', name: 'rental_create_with_book', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function create(Request $request, #[CurrentUser] User $user, ?Book $book = null): Response
    {
        $rental = new Rental();
        
        if ($book) {
            $rental->setBook($book);
        }

        $rental->setOwner($user);
        $form = $this->createForm(RentalType::class, $rental, ['lock_book' => $book !== null]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $selected_book = $form->get('book')->getData();

            if ($this->bookRepository->isCurrentlyRented($selected_book)) {
                $this->addFlash('warning', 'This book is currently borrowed');

                return $this->redirectToRoute('book_index');
            }

            $rental->setCreatedAt(new DateTimeImmutable());
            $rental->setUpdatedAt(new DateTimeImmutable());
            $rental->setRentedAt(new DateTimeImmutable());
            $rental->setDeadline(new DateTimeImmutable('+14 days'));

            $this->rentalRepository->save($rental);

            $this->addFlash('success', 'Rental created successfully');

            $this->mailerService->sendCreatedRental($rental);
            return $this->redirectToRoute('rental_index');
        }

        return $this->render('/rental/create.html.twig',
        [
            'form' => $form,
        ]);
    }

    #[Route('/create-by-token/{token}', name: 'create_with_token', requirements: ['token' => '[A-Za-z0-9]+'], methods: ['GET', 'POST'])]
    public function createWithToken(#[MapEntity(mapping: ['token' => 'content'])] ?RentalToken $token): Response
    {
        if (!$token) {
            $this->addFlash('warning', 'You dont have permission!');
            
            return $this->redirectToRoute('book_index');
        }

        if ($token->getExpirationDate() <= new DateTimeImmutable()) {
            $this->addFlash('warning', 'This link expired!');

            return $this->redirectToRoute('book_index');
        }

        $rental = new Rental();

        $rental->setRentedAt(new DateTimeImmutable());
        $rental->setBook($token->getBook());
        $rental->setOwner($token->getUser());
        $rental->setDeadline(new DateTimeImmutable('+2 weeks'));
        $this->rentalRepository->save($rental);

        $this->addFlash('success', 'Rental created successfully');

        $this->mailerService->sendCreatedRental($rental);

        $bookQueues = $this->bookQueueRepository->findBy([
            'book' => $token->getBook()
        ]);

        foreach ($bookQueues as $bookQueue) {
            $currentPosition = $bookQueue->getPosition();
            $bookQueue->setPosition($currentPosition - 1);
        }

        $userQueue = $this->bookQueueRepository->findOneBy([
            'user' => $token->getUser(),
            'book' => $token->getBook()
        ]);

        $this->bookQueueRepository->delete($userQueue);
        $this->rentalTokenRepository->delete($token);

        $this->addFlash('success', 'book queue and token deleted successfully');

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
            $rental->setReturnedAt(new DateTimeImmutable());

            $this->rentalRepository->save($rental);

            $this->addFlash('success', 'Book returned successfully');

            $book = $rental->getBook();
            $bookQueues = $this->bookQueueRepository->getBookQueue($book);
            if(count($bookQueues) > 0) {
                /** @var BookQueue $bookQueue */
                foreach($bookQueues as $bookQueue) {
                    if ($bookQueue->getPosition() === 1) {
                        $user = $bookQueue->getUser();
                        $token = $this->rentalTokenService->generateToken($book, $user);

                        $this->mailerService->sendBookAvailableNotification($token);
                    }

                    $this->bookQueueRepository->save($bookQueue);
                }
            }

            return $this->redirectToRoute('rental_index');
        }

        return $this->render('/rental/rental_return.html.twig',
        [
            'form' => $form,
            'rental' => $rental
        ]);
    }
}
