<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;
    private $faker;
    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
        $this->faker = Factory::create('fr FR');
    }
    public function load(ObjectManager $manager): void
    {
        $this->laodUser($manager);
    }

    public function laodUser(ObjectManager $manager): void
    {
        for ($i = 1; $i < 100; $i++) {
            $user  = new User;
            $user->setEmail($this->faker->email())
                ->setPassword($this->passwordHasher->hashPassword($user, '123456'))
                ->setIsActivated(true);

            $manager->persist($user);
            $manager->flush();
        }
    }
}
