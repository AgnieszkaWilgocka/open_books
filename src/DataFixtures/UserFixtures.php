<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private Generator $faker;

    const USER_REFERENCE = 'user';
    const ADMIN_REFERENCE = 'admin';

    public function __construct(private UserPasswordHasherInterface $userPasswordHasher) {}
    
    public function load(ObjectManager $manager): void
    {
        $this->faker = Factory::create();

        for ($i = 0; $i < 3; $i++) {
		    $user = new User();
            $user->setEmail(sprintf('user%d@example.com', $i));
            $user->setRoles(['ROLE_USER']);
            $user->setPassword($this->userPasswordHasher->hashPassword(
                $user,
                'user123'
            ));
            $manager->persist($user);
            $this->addReference(self::USER_REFERENCE . '_' . $i, $user);
        }

        for ($i = 0; $i < 3; $i++) {
		    $user = new User();
            $user->setEmail(sprintf('admin%d@example.com', $i));
            $user->setRoles(['ROLE_ADMIN']);
            $user->setPassword($this->userPasswordHasher->hashPassword(
                $user,
                'admin123'
            ));
            $manager->persist($user);
            $this->addReference(self::ADMIN_REFERENCE . '_' . $i, $user);
        }

        $manager->flush();
    }
}
