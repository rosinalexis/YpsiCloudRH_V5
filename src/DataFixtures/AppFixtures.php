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
use App\Entity\Setting;
use App\Security\TokenGenerator;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;
    private \Faker\Generator $faker;
    private TokenGenerator $tokenGenerator;

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
        $this->loadUser($manager);
    }

    public function loadUser(ObjectManager $manager): void
    {
        for ($i = 0; $i < 15; $i++) {
            $user  = new User;
            $user->setEmail($this->faker->email())
                ->setRoles(USER::ROLE_USER)
                ->setPassword($this->passwordHasher->hashPassword($user, '123456'))
                ->setIsActivated($this->faker->randomElement([true, false]))
                ->setProfile($this->getReference("profile$i"))
                ->setJob($this->getReference("job$i"))
                ->addEstablishment($this->getReference("establishment"))
                ->setCurrentEstablishment($user->getEstablishment()[0]->getId());

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
            ->setIsActivated(true)
            ->addEstablishment($this->getReference("establishment"))
            ->setCurrentEstablishment($user->getEstablishment()[0]->getId());

        $manager->persist($user);
        $manager->flush();

        $user  = new User;
        $user->setEmail('testman@test.fr')
            ->setRoles(['ROLE_USER'])
            ->setPassword($this->passwordHasher->hashPassword($user, '123456'))
            ->setIsActivated(true)
            ->addEstablishment($this->getReference("establishment"))
            ->setCurrentEstablishment($user->getEstablishment()[0]->getId());
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
        //récuperation de l'établissement
        $establishment = $this->getReference("establishment");

        for ($i = 0; $i < 15; $i++) {

            //récupération d'une catégorie
            $category = $this->getReference("category" . $this->faker->numberBetween(1,5));

            $job = new Job();
            $job->setTitle($this->faker->jobTitle())
                ->setDescription($this->faker->realText())
                ->setEstablishment($establishment)
                ->setCategory($category);

            $manager->persist($job);
            $manager->flush();

            //partege du job
            $this->setReference("job$i", $job);
        }
    }

    public function loadCategory(ObjectManager $manager): void
    {
        $listCategory = [
            1 => "Informatique",
            2 => "Marketing",
            3 => "Secrétariat",
            4 => "Restaurant",
            5 => "Management"
        ];

        //récuperation de l'établissement
        $establishment = $this->getReference("establishment");

        foreach ($listCategory as $key => $categoryValue) {

            $category = new Category;
            $category->setTitle($categoryValue);
            $category->setDescription($this->faker->realText());
            $category->setEstablishment($establishment);
            $manager->persist($category);
            $manager->flush();

            $this->setReference("category$key", $category);
        }

    }

    public function loadJobAdvert(ObjectManager $manager): void
    {
        //récuperation de l'établissement
        $establishment = $this->getReference("establishment");

        for ($i = 0; $i < 5; $i++) {
            // récuperation de la catégorie
            $category  = $this->getReference("category" . rand(1,5));

            $jobAdvert = new JobAdvert();

            $jobAdvert
                ->setTitle($this->faker->jobTitle())
                ->setPlace($this->faker->country())
                ->setCompagny('YPSI')
                ->setContractType($this->faker->randomElement(['CDD', 'CDI', 'ITERIM']))
                ->setWage($this->faker->numberBetween(1000, 3000) . "€")
                ->setDescription($this->faker->realText())
                ->setPublished($this->faker->randomElement([true, false]))
                ->setCategory($category)
                ->setTasks(["task1", "task2", "task3"])
                ->setRequirements(["requirement1", "requirement2", "requirement3"])
                ->setEstablishment($establishment);

            $manager->persist($jobAdvert);
            $manager->flush();
        }
    }

    public function loadEstablishment(ObjectManager $manager): void
    {

        $configuration = [
            "emailTemplate" => [
                [
                    "title" => "template accusé de réception",
                    "status" => false,
                    "object" => " accusé de réception test",
                    "content" => [
                        "ops" => []
                    ],
                    "htmlContent" => "<p>Bonjour  %user%, </p> <br/> <p>je suis la version 1</p> <br/> <p>cordialement</p>"
                ],
                [
                    "title" => "template de date",
                    "status" => false,
                    "object" => "template accusé de réception date",
                    "content" => [
                        "ops" => []
                    ],
                    "htmlContent" => "<p>Bonjour  %user%, </p> <br/> <p>je suis la version 2</p> <br/> <p>cordialement</p>"
                ]
            ],
            "equipmentConfig" => [
                [
                    "title" => "Informatique",
                    "equipments" => ["ordinateur", "souris", "tableau"]
                ],
                [
                    "title" => "Marketing",
                    "equipments" => ["ordinateur mac", "souris", "tableau"]
                ]
            ],

            "documentConfig" => [
                [
                    "title" => "Droit à l'image",
                    "section" => "Droit à l'image",
                    "content" => "Contenu  de l'application"
                ]
            ],
            "helpDocumentConfig" => [
                [
                    "title" => "test aide 1",
                    "helpType" => "test aide regionnale",
                    "description" => "test de description"
                ]
            ],
        ];

        $establishment = new Establishment();
        $establishment->setSiret($this->faker->siret())
            ->setName("Etablissement Test")
            ->setPhone($this->faker->phoneNumber())
            ->setDepartmentName($this->faker->departmentName())
            ->setDepartmentNumber(intval($this->faker->departmentNumber()))
            ->setSetting($configuration)
            ->setRegion($this->faker->region());

        $manager->persist($establishment);
        $manager->flush();

        $this->setReference("establishment", $establishment);
    }
}
