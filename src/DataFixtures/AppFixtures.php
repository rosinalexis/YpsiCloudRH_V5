<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Faker\Factory;
use App\Entity\Job;
use App\Entity\User;
use App\Entity\Image;
use App\Entity\Profile;
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
        $this->faker = Factory::create('fr_FR');
        $this->tokenGenerator = $tokenGenerator;
    }

    public function load(ObjectManager $manager): void
    {
        $this->loadCategory($manager);
        $this->loadJob($manager);
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
                ->setProfile($this->getReference("profile$i"))
                ->setJob($this->getReference("job$i"));

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


    public function loadJob(ObjectManager $manager): void
    {
        for ($i = 0; $i < 5; $i++) {
            $job = new Job();
            $job->setTitle($this->faker->jobTitle())
                ->setDescription($this->faker->realText())
                ->setCategory($this->getReference("category$i"));

            $manager->persist($job);
            $manager->flush();

            $this->setReference("job$i", $job);
        }
    }

    public function loadCategory(ObjectManager $manager): void
    {
        $listCategory = [
            0 => "Informatque",
            1 => "Marketing",
            2 => "Secrétariat",
            3 => "Restaurant",
            4 => "Management"
        ];

        foreach ($listCategory as $key => $categoryValue) {

            $category = new Category;
            $category->setTitle($categoryValue);
            $category->setDescription($this->faker->realText());
            $manager->persist($category);
            $manager->flush();

            $this->setReference("category$key", $category);
        }
    }
}
