<?php

namespace App\Repository;

use App\Entity\Book;
use App\Entity\BookQueue;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BookQueue>
 */
class BookQueueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, BookQueue::class);
    }

    public function queryAll(): array
    {
        return $this->createQueryBuilder('bq')
        ->select('bq', 'book', 'user')
        ->join('bq.user', 'user')
        ->join('bq.book', 'book')
        ->orderBy('bq.createdAt', 'DESC')
        ->getQuery()
        ->getResult();
    }

    public function queryQueuedBooks(Book $book, ?User $user = null): array
    {
        $qb = $this->createQueryBuilder('bq')
        ->select('bq')
        ->join('bq.book', 'book')
        ->andWhere('bq.book = :book')
        ->setParameter('book', $book);

        if ($user) {
            $qb = $qb->join('bq.user', 'user')
            ->andWhere('bq.user = :user')
            ->setParameter('user', $user);
        }
        
        return $qb->getQuery()->getResult();
    }

    public function getFirstPosition(Book $book): BookQueue
    {
        return $this->createQueryBuilder('bq')
        ->select('bq')
        ->join('bq.book', 'book')
        ->andWhere('bq.book = :book')
        ->andWhere('bq.position = :position')
        ->setParameter('book', $book)
        ->setParameter('position', 1)
        ->getQuery()
        ->getOneOrNullResult();
    }

    public function save(BookQueue $bookQueue): void
    {
        $this->entityManager->persist($bookQueue);
        $this->entityManager->flush();
    }

    public function delete(BookQueue $bookQueue): void
    {
        $this->entityManager->remove($bookQueue);
        $this->entityManager->flush();
    }

//    /**
//     * @return BookQueue[] Returns an array of BookQueue objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?BookQueue
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
