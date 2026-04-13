<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Rental;
use App\Entity\User;
use App\Form\Type\RentalType;
use App\Repository\BookRepository;
use App\Repository\RentalRepository;
use App\Security\Voter\RentalVoter;
use DateTimeImmutable;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/rentals')]
class RentalController extends AbstractController
{
    public function __construct(private RentalRepository $rentalRepository, private BookRepository $bookRepository, private MailerInterface $mailer)
    {}

    #[Route('/', name: 'rental_index', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function index(#[CurrentUser] User $user): Response
    {
        $rentals = $this->rentalRepository->queryAll($user);
        // $rentals = $this->rentalRepository->findAll();

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

            $this->rentalRepository->save($rental);

            $this->addFlash('success', 'Rental created successfully');
            $email = (new TemplatedEmail())
                ->from('openbooksapp@example.com')
                ->to($user->getEmail())
                ->subject('Time for another adventure!')
                ->htmlTemplate('mailer/rental_create.html.twig')
                ->context([
                    'deadline' => new \DateTime('+7 days'),
                    'username' => $user->getEmail(),
                    'book' => $book->getTitle()
                ]);
            
            $this->mailer->send($email);

            return $this->redirectToRoute('rental_index');
        }

        return $this->render('/rental/create.html.twig',
        [
            'form' => $form,
        ]);
    }

    #[Route('/returnBook/{id}', name: 'rental_return', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'PUT'])]
    #[IsGranted(RentalVoter::RENTAL_RETURN, subject: 'rental')]
    public function returnBook(Request $request, Rental $rental): Response
    {
        if (!$rental->canBeReturned()) {

            $this->addFlash('warning', 'Rental already returned');
            return $this->redirectToRoute('rental_index');
            // throw new Exception('rental already returned');    
        }

        $form = $this->createForm(FormType::class, $rental, 
        [
            'action' => $this->generateUrl('rental_return', ['id' => $rental->getId()]),
            'method' => 'PUT'
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $rental->setCreatedAt(new DateTimeImmutable());
            $rental->setUpdatedAt(new DateTimeImmutable());
            $rental->setReturnedAt(new DateTimeImmutable());

            $this->rentalRepository->save($rental);

            $this->addFlash('success', 'Book returned successfully');

            return $this->redirectToRoute('rental_index');
        }

        return $this->render('/rental/rental_return.html.twig',
        [
            'form' => $form,
            'rental' => $rental
        ]);
    }
}
