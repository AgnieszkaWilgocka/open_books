<?php

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CategoryRepositoryTest extends KernelTestCase
{
    public function testReturnsCategoriesByGivenLimit(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $categoryFirst = $this->createCategory();
        $categorySecond = $this->createCategory();
        $categoryThird = $this->createCategory();

        $entityManager = $container->get(EntityManagerInterface::class);
        $categoryRepository = $container->get(CategoryRepository::class);

        $entityManager->persist($categoryFirst);
        $entityManager->persist($categorySecond);
        $entityManager->persist($categoryThird);
        $entityManager->flush();

        $result = $categoryRepository->queryByLimit(2);

        $this->assertContains($categoryFirst, $result);
        $this->assertContains($categorySecond, $result);
        $this->assertNotContains($categoryThird, $result);
    }

    private function createCategory(): Category
    {
        $category = new Category();
        $category->setTitle('Test Category');
        $category->setColor('#dddddd');

        return $category;
    }
}
