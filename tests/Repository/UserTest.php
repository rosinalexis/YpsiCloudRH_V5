<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;



class UserTest extends KernelTestCase
{

    private $entityManager;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();
    }

    public function testUserCount(): void
    {
        // récuperation des utilisateurs
        $users = static::getContainer()->get(UserRepository::class)->count([]);
        $this->assertEquals(3, $users);
    }

    public function testUserAdd(): void
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

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->close();
        $this->entityManager = null;
    }
}
