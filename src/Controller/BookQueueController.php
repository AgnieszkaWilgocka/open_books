<?php

namespace App\Controller;

use App\Repository\BookQueueRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/book-queues')]
class BookQueueController extends AbstractController
{
    public function __construct(private BookQueueRepository $bookQueueRepository) {}

    #[Route('/', name: 'book_queue_index', methods: ['GET'])]
    public function index(): Response
    {
        $bookQueues = $this->bookQueueRepository->queryAll();

        return $this->render('/book_queue/index.html.twig', [
            'bookQueues' => $bookQueues
        ]);
    }   
}