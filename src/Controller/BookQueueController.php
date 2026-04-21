<?php

namespace App\Controller;

use App\Entity\Book;
use App\Entity\BookQueue;
use App\Entity\User;
use App\Repository\BookQueueRepository;
use App\Service\MailerService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\CurrentUser;

#[Route('/book-queues')]
class BookQueueController extends AbstractController
{
    public function __construct(private BookQueueRepository $bookQueueRepository, private EntityManagerInterface $entityManager, private MailerService $mailerService) {}

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
        $bookQueue = new BookQueue();
        $bookQueue->setBook($book);
        $bookQueue->setUser($user);
        $bookQueue->setMissingOpportunity(0);
        $bookQueue->setCreatedAt(new DateTimeImmutable());

        $queue = $this->bookQueueRepository->getBookQueue($book);
        $bookQueue->setPosition(count($queue) + 1);

        $this->mailerService->sendSignUpQueue($bookQueue);

        $this->entityManager->persist($bookQueue);
        $this->entityManager->flush();

        $this->addFlash('success', 'Submitted to queue successully');

        return $this->redirectToRoute('book_index');
    }
}