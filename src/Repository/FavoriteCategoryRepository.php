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

    public function queryAll(User $owner): array
    {
        return $this->createQueryBuilder('fc')
        ->select('fc', 'partial user.{id, email}', 'partial category.{id, title, color}')
        ->join('fc.owner', 'user')
        ->join('fc.category', 'category')
        ->andWhere('fc.owner = :owner')
        ->setParameter('owner', $owner)
        ->orderBy('fc.createdAt', 'DESC')
        ->getQuery()
        ->getResult();
    }

    public function queryRandom(User $user): FavoriteCategory
    {
        return $this->createQueryBuilder('fc')
            // ->select('fc', 'partial user.{id}', 'partial category.{id}')
            ->join('fc.owner', 'user')
            // ->join('fc.category', 'category')
            ->andWhere('fc.owner = :owner')
            ->setParameter('owner', $user)
            ->orderBy('RAND()', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(FavoriteCategory $favoriteCategory): void
    {
        $this->entityManager->persist($favoriteCategory);
        $this->entityManager->flush();
    }

    public function delete(FavoriteCategory $favoriteCategory): void
    {
        $this->entityManager->remove($favoriteCategory);
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
