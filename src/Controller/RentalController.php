<?php

namespace App\Controller;

use App\Repository\RentalRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/rentals')]
class RentalController extends AbstractController
{
    public function __construct(private RentalRepository $rentalRepository)
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


}