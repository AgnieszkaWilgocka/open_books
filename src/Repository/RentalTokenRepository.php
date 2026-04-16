<?php

namespace App\Repository;

use App\Entity\RentalToken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RentalToken>
 */
class RentalTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RentalToken::class);
    }

        public function queryAll(): array
    {
        return $this->createQueryBuilder('rt')
        ->select('rt', 'book', 'user')
        ->join('rt.user', 'user')
        ->join('rt.book', 'book')
        ->orderBy('rt.createdAt', 'DESC')
        ->getQuery()
        ->getResult();
    }
    //    /**
    //     * @return RentalToken[] Returns an array of RentalToken objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('r.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?RentalToken
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
