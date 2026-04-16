<?php

namespace App\Repository;

use App\Entity\BookQueue;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BookQueue>
 */
class BookQueueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
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
