<?php

namespace App\Tests\Entity;

use App\Entity\User;
use App\Kernel;
use Symfony\Component\Validator\Validation;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserEntityTest extends KernelTestCase
{

    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        // $this->validator = Validation::createValidator();
        $this->validator = $kernel->getContainer()->get('validator');
    }

    public function testUserEntityIsValid(): void
    {
        $user = $this->getEntity();
        $this->getValidationErrors($user, 0);
    }

    public function testUserEntityEmailIsBlank(): void
    {
        $user = $this->getEntity()->setEmail('');
        $this->getValidationErrors($user, 1);
    }

    public function testUserEntityEmailIsInvalid(): void
    {
        $user = $this->getEntity()->setEmail('adb@aba');
        $this->getValidationErrors($user, 1);
    }

    public function testUserEntityRoleIsBlank(): void
    {
        $user = $this->getEntity()->setRoles([]);
        $this->getValidationErrors($user, 2);
    }

    public function testUserEntityRoleIsInvalid(): void
    {
        $user = $this->getEntity()->setRoles(["ROLE_TEST"]);
        $this->getValidationErrors($user, 1);
    }

    public function testUserEntityPasswordIsBlank(): void
    {
        $user = $this->getEntity()->setPlainPassword('');
        $this->getValidationErrors($user, 1);
    }

    public function testUserEntityPasswordIsInvalid(): void
    {
        $user = $this->getEntity()->setPlainPassword('123');
        $this->getValidationErrors($user, 1);
    }


    private function getEntity(): User
    {
        $user = new User();
        $user->setEmail("admin@apitest.fr")
            ->setIsActivated(true)
            ->setPlainPassword("123456")
            ->setRoles(["ROLE_USER"])
            ->setCreatedAt(new \DateTimeImmutable())
            ->setUpdatedAt(new \DateTimeImmutable());

        return $user;
    }

    private function getValidationErrors(User $user, int $numberOfExpectedErrors): ConstraintViolationList
    {
        $errors  = $this->validator->validate($user);

        $messages = [];
        /**
         * @var ConstraintViolation $error
         */
        foreach ($errors as $error) {
            $messages[] = $error->getPropertyPath() . ' => ' . $error->getMessage();
        }

        $this->assertCount($numberOfExpectedErrors, $errors, implode(', ', $messages));

        return $errors;
    }


    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
