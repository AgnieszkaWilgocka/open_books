<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\User;
use App\Repository\BookQueueRepository;
use App\Service\BookQueueFlowService;
use App\Service\MailerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/book-queues')]
class BookQueueController extends AbstractController
{
    public function __construct(private BookQueueRepository $bookQueueRepository, private BookQueueFlowService $bookQueueFlowService) {}

    #[Route('/', name: 'book_queue_index', methods: ['GET'])]
    public function index(): Response
    {
        $bookQueues = $this->bookQueueRepository->queryAll();

        return $this->render('/book_queue/index.html.twig', [
            'bookQueues' => $bookQueues
        ]);
    }

    #[Route('/create/{id}', name:'book_queue_create', requirements: ['id' => '[1-9]\d*'], methods: ['GET', 'POST'])]
    public function create(Book $book, #[CurrentUser] User $user): Response
    {
        $this->bookQueueFlowService->joinToQueue($book, $user);
        $this->addFlash('success', 'Submitted to queue successully');

        return $this->redirectToRoute('book_index');
    }
}
