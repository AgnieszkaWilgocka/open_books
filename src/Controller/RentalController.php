<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\Rental;
use App\Enum\BookStatusEnum;
use App\Form\Type\RentalType;
use App\Repository\BookRepository;
use App\Repository\RentalRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/rentals')]
class RentalController extends AbstractController
{
    public function __construct(private RentalRepository $rentalRepository, private BookRepository $bookRepository)
    {}

    #[Route('/', name: 'rental_index', methods: ['GET'])]
    public function index(): Response
    {
        $rentals = $this->rentalRepository->findAll();

        return $this->render('/rental/index.html.twig',
        [
            'rentals' => $rentals
        ]);
    }

    #[Route('/create', name: 'rental_create', methods: ['GET', 'POST'])]
    #[Route('/create/{id}', name: 'rental_create_with_book', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'POST'])]
    public function create(Request $request, ?Book $book = null): Response
    {
        $rental = new Rental();

        if ($book) {
            $rental->setBook($book);
        }

        $form = $this->createForm(RentalType::class, $rental, ['lock_book' => $book !== null]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $rental->setCreatedAt(new DateTimeImmutable());
            $rental->setUpdatedAt(new DateTimeImmutable());
            $rental->setRentedAt(new DateTimeImmutable());

            $this->rentalRepository->save($rental);

            return $this->redirectToRoute('rental_index');
        }

        return $this->render('/rental/create.html.twig',
        [
            'form' => $form,
        ]);
    }

    #[Route('/returnBook/{id}', name: 'rental_return', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'PUT'])]
    public function returnBook(Request $request, Rental $rental): Response
    {
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

            return $this->redirectToRoute('rental_index');
        }

        return $this->render('/rental/rental_return.html.twig',
        [
            'form' => $form,
            'rental' => $rental
        ]);
    }

}
