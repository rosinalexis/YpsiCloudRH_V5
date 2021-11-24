<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{

    public function testUserIsCreate(): void
    {
        $user = new User;
        $user->setEmail("admin@api-test.fr");
        $user->setPassword("123456");
        $user->setRoles(["ROLE_USER"]);

        $this->assertEquals(NULL, $user->getId());
        $this->assertEquals("admin@api-test.fr", $user->getUserIdentifier());
        $this->assertEquals("admin@api-test.fr", $user->getUsername());
        $this->assertEquals("123456", $user->getPassword());
        $this->assertEquals(["ROLE_USER"], $user->getRoles());
        $this->assertEquals(NULL, $user->getCreatedAt());
        $this->assertEquals(NULL, $user->getUpdatedAt());
    }

    public function testUserIsFalse(): void
    {
        $user = new User;
        $user->setEmail("admin@api-test.fr");
        $user->setPassword("123456");

        $this->assertFalse($user->getId() == 1);
        $this->assertFalse($user->getUserIdentifier() == "admin@api-test");
        $this->assertFalse($user->getPassword() == "12345");
        $this->assertFalse($user->getRoles() == "ROLE_USER");
        $this->assertFalse($user->getCreatedAt() instanceof \DateTimeImmutable);
        $this->assertFalse($user->getUpdatedAt() instanceof \DateTimeImmutable);
    }

    public function testUserIsEmpty(): void
    {
        $user = new User;

        $this->assertEmpty($user->getId());
        $this->assertEmpty($user->getUserIdentifier());
        $this->assertEmpty($user->getRoles());
        //$this->assertNull($user->getPassword());
        $this->assertEmpty($user->getCreatedAt());
        $this->assertEmpty($user->getUpdatedAt());
    }
}
