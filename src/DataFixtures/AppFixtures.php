<?php

namespace App\DataFixtures;

use Faker\Factory;
use App\Entity\Job;
use App\Entity\User;
use App\Entity\Image;
use App\Entity\Profile;
use App\Entity\Category;
use App\Entity\JobAdvert;

use App\Entity\Establishment;
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
        $this->loadEstablishment($manager);
        $this->loadCategory($manager);
        $this->loadJobAdvert($manager);
        $this->loadJob($manager);
        $this->loadProfile($manager);
        $this->laodUser($manager);
    }

    public function laodUser(ObjectManager $manager): void
    {
        for ($i = 0; $i < 15; $i++) {
            $user  = new User;
            $user->setEmail($this->faker->email())
                ->setRoles(USER::ROLE_USER)
                ->setPassword($this->passwordHasher->hashPassword($user, '123456'))
                ->setIsActivated($this->faker->randomElement([true, false]))
                ->setProfile($this->getReference("profile$i"))
                ->setJob($this->getReference("job$i"))
                ->addEstablishment($this->getReference("establishment" . rand(0, 2)));

            if (!$user->getIsActivated()) {
                $user->setConfirmationToken(
                    $this->tokenGenerator->getRandomeSecureToken()
                );
            }

            // switch ($i) {
            //     case $i < 5:
            //         $user->addEstablishment($this->getReference("establishment0"));
            //         break;

            //     case $i > 5 && $i < 10:
            //         $user->addEstablishment($this->getReference("establishment1"));
            //         break;

            //     case $i > 10:
            //         $user->addEstablishment($this->getReference("establishment2"));
            //         break;

            //     default:
            //         $user->addEstablishment($this->getReference("establishment0"));
            //         break;
            // }
            $manager->persist($user);
            $manager->flush();
        }

        $user  = new User;
        $user->setEmail('admin@admin.fr')
            ->setRoles(USER::ROLE_ADMIN)
            ->setPassword($this->passwordHasher->hashPassword($user, '123456'))
            ->setIsActivated(true)
            ->addEstablishment($this->getReference("establishment0"))
            ->addEstablishment($this->getReference("establishment1"))
            ->addEstablishment($this->getReference("establishment2"));
        $manager->persist($user);
        $manager->flush();

        $user  = new User;
        $user->setEmail('testman@test.fr')
            ->setRoles(['ROLE_USER'])
            ->setPassword($this->passwordHasher->hashPassword($user, '123456'))
            ->setIsActivated(true)
            ->addEstablishment($this->getReference("establishment0"));


        $manager->persist($user);
        $manager->flush();
    }

    public function loadProfile(ObjectManager $manager): void
    {
        for ($i = 0; $i < 15; $i++) {
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
        for ($i = 0; $i < 15; $i++) {
            $job = new Job();
            $job->setTitle($this->faker->jobTitle())
                ->setDescription($this->faker->realText())
                ->setCategory($this->getReference("category" . $this->faker->randomElement([0, 1, 2, 3, 4])));

            $manager->persist($job);
            $manager->flush();

            $this->setReference("job$i", $job);
        }
    }

    public function loadCategory(ObjectManager $manager): void
    {
        $listCategory = [
            0 => "Informatique",
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

    public function loadJobAdvert(ObjectManager $manager): void
    {
        for ($i = 0; $i < 5; $i++) {

            $jobAdvert = new JobAdvert();

            $jobAdvert->setTitle($this->faker->jobTitle())
                ->setPlace($this->faker->country())
                ->setCompagny('YPSI')
                ->setContractType($this->faker->randomElement(['CDD', 'CDI', 'ITERIM']))
                ->setWage($this->faker->numberBetween(1000, 3000) . "€")
                ->setDescription($this->faker->realText())
                ->setPublished($this->faker->randomElement([true, false]))
                ->setCategory($this->getReference("category$i"))
                ->setTasks(["task1", "task2", "task3"])
                ->setRequirements(["requirement1", "requirement2", "requirement3"]);

            $manager->persist($jobAdvert);
            $manager->flush();
        }
    }

    public function loadEstablishment(ObjectManager $manager): void
    {
        for ($i = 0; $i < 3; $i++) {
            $establishment = new Establishment();
            $establishment->setSiret($this->faker->siret())
                ->setName($this->faker->company())
                ->setPhone($this->faker->phoneNumber())
                ->setDepartmentName($this->faker->departmentName())
                ->setDepartmentNumber($this->faker->departmentNumber())
                ->setRegion($this->faker->region());

            $manager->persist($establishment);
            $manager->flush();

            $this->setReference("establishment$i", $establishment);
        }
    }
}
