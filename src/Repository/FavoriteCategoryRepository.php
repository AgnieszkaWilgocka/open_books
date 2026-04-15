<?php

namespace App\Repository;

use App\Entity\Category;
use App\Entity\FavoriteCategory;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FavoriteCategory>
 */
class FavoriteCategoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry, private EntityManagerInterface $entityManager)
    {
        parent::__construct($registry, FavoriteCategory::class);
    }

    public function save(FavoriteCategory $favoriteCategory): void
    {
        $favoriteCategory->setCreatedAt(new DateTimeImmutable());
        $favoriteCategory->setUpdatedAt(new DateTimeImmutable());
        
        $this->entityManager->persist($favoriteCategory);
        $this->entityManager->flush();
    }

    //    /**
    //     * @return FavoriteCategory[] Returns an array of FavoriteCategory objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('f.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?FavoriteCategory
    //    {
    //        return $this->createQueryBuilder('f')
    //            ->andWhere('f.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
