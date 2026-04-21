<?php

namespace App\Controller;

use App\Repository\RentalTokenRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/rental-tokens')]
class RentalTokenController extends AbstractController
{
    public function __construct(private RentalTokenRepository $rentalTokenRepository) {}

    #[Route('/', name: 'rental_tokens_index', methods: ['GET'])]
    public function index(): Response
    {
        $rental_tokens = $this->rentalTokenRepository->queryAll();

        return $this->render('/rental_token/index.html.twig',
        [
            'rentalTokens' => $rental_tokens
        ]);
    }

    
}
