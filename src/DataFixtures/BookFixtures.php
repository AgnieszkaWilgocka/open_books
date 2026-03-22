<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\Category;
use App\Service\FileUploaderHelper;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;

class BookFixtures extends Fixture implements DependentFixtureInterface
{

    public function __construct(private FileUploaderHelper $fileUploader) {}

    const BOOK_REFERENCE = 'book';

    private $bookImages = [
        'book-adventure.jpg',
        'book-fantasy.jpg',
        'book-kitchen.jpg',
        'book-recipies.jpg'
    ];

    private Generator $faker;


	public function load(ObjectManager $manager): void
	{
        $this->faker = Factory::create();

		for ($i = 0; $i < 5; $i++) {
            $book = new Book();
            $book->setTitle($this->faker->sentence(2));
            $book->setCreatedAt(DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-30 days', 'now')));
            $book->setUpdatedAt(DateTimeImmutable::createFromMutable($this->faker->dateTimeBetween('-30 days', 'now')));
            $book->setYearOfRelease($this->faker->numberBetween(1900, 2026));
            $book->setPages($this->faker->numberBetween(100, 500));
            $book->setCategory($this->getReference(CategoryFixtures::CATEGORY_REFERENCE . '_' . $this->faker->numberBetween(0, 9), Category::class));

            $imageFilename = $this->fakeFileUpload();
            $book->setImageFileName($imageFilename);

            $this->addReference(self::BOOK_REFERENCE . '_' . $i, $book);
            $manager->persist($book);
        }

        $manager->flush();
	}

    private function fakeFileUpload(): string
    {
        $randomBookImage = $this->faker->randomElement($this->bookImages);
        $filesystem = new Filesystem();
        $targetPath = sys_get_temp_dir() . '/' . $randomBookImage;
        $filesystem->copy(__DIR__ . '/images/' . $randomBookImage, $targetPath, true);

        return $this->fileUploader->uploadFile(new File($targetPath));
    }

    public function getDependencies(): array
    {
        return [CategoryFixtures::class];
    }
}
