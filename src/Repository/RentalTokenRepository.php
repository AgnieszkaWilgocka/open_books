<?php

namespace App\Repository;

use App\Entity\RentalToken;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RentalToken>
 */
class RentalTokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private EntityManagerInterface $entityManager)
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

    public function queryExpiredTokens(): array
    {
        return $this->createQueryBuilder('rt')
        // ->select('rt')
        ->andWhere('rt.expiration_date <= :date')
        ->setParameter('date', new DateTimeImmutable())
        ->getQuery()
        ->getResult();
    }

    public function save(RentalToken $rentalToken): void
    {
        $rentalToken->setCreatedAt(new DateTimeImmutable());

        $this->entityManager->persist($rentalToken);
        $this->entityManager->flush();
    }


    public function delete(RentalToken $rentalToken): void
    {
        $this->entityManager->remove($rentalToken);
        $this->entityManager->flush();
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
