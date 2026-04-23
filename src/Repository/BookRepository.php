<?php

namespace App\Repository;

use App\Entity\Book;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, Book::class);
    }

    public function queryAll(): array
    {
        return $this->createQueryBuilder('book')
            ->select('book', 'partial rentals.{id, returnedAt}')
            ->leftJoin('book.rentals', 'rentals', 'WITH', 'rentals.returnedAt IS NULL')
            ->orderBy('book.title', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function queryAvailable(): QueryBuilder
    {
        return $this->createQueryBuilder('book')
            ->select('book', 'rentals')
            ->leftJoin('book.rentals', 'rentals', 'WITH', 'rentals.returnedAt IS NULL')
            ->andWhere('rentals.id IS NULL' )
            ->orderBy('book.title', 'ASC');
    }

    public function isCurrentlyRented(Book $book): bool
    {
        return (bool) $this->createQueryBuilder('book')
            ->select('1')
            ->join('book.rentals', 'rentals')
            ->andWhere('book = :book')
            ->andWhere('rentals.returnedAt IS NULL')
            ->setParameter('book', $book)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(Book $book): void
    {        
        $this->entityManager->persist($book);
        $this->entityManager->flush();
    }

    public function delete(Book $book): void
    {
        $this->entityManager->remove($book);
        $this->entityManager->flush();
    }

    //    /**
    //     * @return Book[] Returns an array of Book objects
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

    //    public function findOneBySomeField($value): ?Book
    //    {
    //        return $this->createQueryBuilder('b')
    //            ->andWhere('b.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
