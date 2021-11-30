<?php

namespace App\DataFixtures;

use App\Entity\Image;
use App\Entity\Profile;
use Faker\Factory;
use App\Entity\User;
use App\Security\TokenGenerator;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $passwordHasher;
    private $faker;
    private $tokenGenerator;

    public function __construct(UserPasswordHasherInterface $passwordHasher, TokenGenerator $tokenGenerator)
    {
        $this->passwordHasher = $passwordHasher;
        $this->faker = Factory::create('fr FR');
        $this->tokenGenerator = $tokenGenerator;
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadProfile($manager);
        $this->laodUser($manager);
    }

    public function laodUser(ObjectManager $manager): void
    {
        for ($i = 0; $i < 5; $i++) {
            $user  = new User;
            $user->setEmail($this->faker->email())
                ->setRoles(USER::ROLE_USER)
                ->setPassword($this->passwordHasher->hashPassword($user, '123456'))
                ->setIsActivated($this->faker->randomElement([true, false]))
                ->setProfile($this->getReference("profile$i"));

            if (!$user->getIsActivated()) {
                $user->setConfirmationToken(
                    $this->tokenGenerator->getRandomeSecureToken()
                );
            }
            $manager->persist($user);
            $manager->flush();
        }

        $user  = new User;
        $user->setEmail('admin@admin.fr')
            ->setRoles(USER::ROLE_ADMIN)
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
        for ($i = 0; $i < 5; $i++) {
            $profile = new Profile;
            $profile->setFirstname($this->faker->firstName())
                ->setLastname($this->faker->lastName())
                ->setGender($this->faker->randomElement([PROFILE::GENDER_MALE, PROFILE::GENDER_FEMALE, PROFILE::GENDER_GIRL]))
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
