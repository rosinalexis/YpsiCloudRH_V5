<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;



class UserTest extends KernelTestCase
{
    private $entityManager;

    private UserRepository $userRepo;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->userRepo = static::getContainer()->get(UserRepository::class);
    }

    public function testUserRepositoryCount(): void
    {
        // récuperation des utilisateurs
        $users = $this->userRepo->count([]);
        $this->assertEquals(4, $users);
    }

    public function testUserRepositoryAdd(): void
    {
        //insertion de l'utilisateur
        $userTest = new User;
        $userTest->setEmail("test@test.fr");
        $userTest->setPassword("123456");
        $userTest->setRoles(["ROLE_USER"]);
        $userTest->setCreatedAt(new \DateTimeImmutable);
        $userTest->setUpdatedAt(new \DateTimeImmutable);
        $userTest->setIsActivated(true);


        $this->entityManager->persist($userTest);
        $this->entityManager->flush();

        // récuperation de l'utilisateur
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'test@test.fr']);

        $this->assertSame("test@test.fr", $user->getEmail());
        $this->assertSame("123456", $user->getPassword());
        $this->assertSame(["ROLE_USER"], $user->getRoles());
    }

    public function testUserRepositoryUpdate(): void
    {
        $user = $this->userRepo->findOneBy(['email' => 'testman@test.fr']);
        $user->setIsActivated(false);

        $this->entityManager->flush();

        $user = $this->userRepo->findOneBy(['email' => 'testman@test.fr']);
        $this->assertTrue($user->getIsActivated() == false);
    }


    public function testUserRepositoryRemove(): void
    {
        $user = $this->userRepo->findOneBy(['email' => 'testman@test.fr']);
        $this->entityManager->remove($user);
        $this->entityManager->flush();

        $this->assertNull($this->userRepo->findOneBy(['email' => 'test@test.fr']));
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }
}
