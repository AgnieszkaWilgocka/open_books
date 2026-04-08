<?php

namespace App\Repository;

use App\Entity\Rental;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Rental>
 */
class RentalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Rental::class);
    }

    public function save(Rental $rental): void
    {
        $this->entityManager->persist($rental);
        $this->entityManager->flush();
    }

    public function queryAll(User $owner): array
    {
        return $this->createQueryBuilder('rental')
            ->select('rental', 'partial user.{id, email}')
            ->join('rental.owner', 'user')
            ->andWhere('rental.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('rental.rentedAt', 'ASC')
            ->getQuery()
            ->getResult();
    }

    //    /**
    //     * @return Rental[] Returns an array of Rental objects
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

    //    public function findOneBySomeField($value): ?Rental
    //    {
    //        return $this->createQueryBuilder('r')
    //            ->andWhere('r.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
