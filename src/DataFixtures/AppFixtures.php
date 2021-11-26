<?php

namespace App\DataFixtures;

use App\Entity\Profile;
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
        $this->loadProfile($manager);
        $this->laodUser($manager);
    }

    public function laodUser(ObjectManager $manager): void
    {
        for ($i = 0; $i < 3; $i++) {
            $user  = new User;
            $user->setEmail($this->faker->email())
                ->setRoles(['ROLE_USER'])
                ->setPassword($this->passwordHasher->hashPassword($user, '123456'))
                ->setIsActivated(true)
                ->setProfile($this->getReference("profile$i"));
            $manager->persist($user);
            $manager->flush();
        }

        $user  = new User;
        $user->setEmail('admin@admin.fr')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($this->passwordHasher->hashPassword($user, '123456'))
            ->setIsActivated(true);
        $manager->persist($user);
        $manager->flush();

        $user  = new User;
        $user->setEmail('testman@test.fr')
            ->setRoles(['ROLE_USER'])
            ->setPassword($this->passwordHasher->hashPassword($user, '123456'))
            ->setIsActivated(true);
        $manager->persist($user);
        $manager->flush();
    }

    public function loadProfile(ObjectManager $manager): void
    {
        for ($i = 0; $i < 3; $i++) {
            $profile = new Profile;
            $profile->setFirstname($this->faker->firstName())
                ->setLastname($this->faker->lastName())
                ->setGender($this->faker->randomElement(['Monsieur', 'Madame']))
                ->setAddress($this->faker->address())
                ->setPhone($this->faker->phoneNumber())
                ->setBirthdate(new \DateTimeImmutable())
                ->setDescription($this->faker->realText(100));
            $manager->persist($profile);
            $manager->flush();

            $this->setReference("profile$i", $profile);
        }
    }
}
