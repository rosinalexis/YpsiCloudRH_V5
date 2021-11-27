<?php

namespace App\Tests\Entity;

use App\Entity\Profile;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ProfileEntityTest extends KernelTestCase
{
    const FAKETEXT = "Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec.";

    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->validator = $kernel->getContainer()->get('validator');
    }

    public function testProfileEntityIsValid(): void
    {
        $profile = $this->getEntity();
        $this->getValidationErrors($profile, 0);
    }

    public function testProfileEntityLastnameIsBlank(): void
    {
        $profile = $this->getEntity()->setLastname("");
        $this->getValidationErrors($profile, 2);
    }

    public function testProfileEntityLastnameIsInvalid(): void
    {
        $profile = $this->getEntity()->setLastname("Az");
        $this->getValidationErrors($profile, 1);
    }

    public function testProfileEntityLastnameIsToLong(): void
    {
        $profile = $this->getEntity()->setFirstname(self::FAKETEXT);
        $this->getValidationErrors($profile, 1);
    }

    public function testProfileEntityFristnameIsBlank(): void
    {
        $profile = $this->getEntity()->setFirstname("");
        $this->getValidationErrors($profile, 2);
    }

    public function testProfileEntityFristnameIsInvalid(): void
    {
        $profile = $this->getEntity()->setFirstname("Az");
        $this->getValidationErrors($profile, 1);
    }

    public function testProfileEntityFristnameIsToLong(): void
    {
        $profile = $this->getEntity()->setFirstname(self::FAKETEXT);
        $this->getValidationErrors($profile, 1);
    }

    public function testProfileEntityGenderIsBlank(): void
    {
        $profile = $this->getEntity()->setGender("");
        $this->getValidationErrors($profile, 2);
    }

    public function testProfileEntityGenderIsInvalid(): void
    {
        $profile = $this->getEntity()->setGender("Mister_you");
        $this->getValidationErrors($profile, 1);
    }

    public function testProfileEntityPhoneIsBlank(): void
    {
        $profile = $this->getEntity()->setPhone("");
        $this->getValidationErrors($profile, 2);
    }

    public function testProfileEntityPhoneIsInvalidToShort(): void
    {
        $profile = $this->getEntity()->setPhone("22");
        $this->getValidationErrors($profile, 1);
    }

    public function testProfileEntityPhoneIsInvalidToLong(): void
    {
        $profile = $this->getEntity()->setPhone(self::FAKETEXT);
        $this->getValidationErrors($profile, 1);
    }

    public function testProfileEntityAddressIsBlank(): void
    {
        $profile = $this->getEntity()->setAddress("");
        $this->getValidationErrors($profile, 2);
    }

    public function testProfileEntityAddressIsInvalidToShort(): void
    {
        $profile = $this->getEntity()->setAddress("22");
        $this->getValidationErrors($profile, 1);
    }

    public function testProfileEntityDateIsInvalid(): void
    {
        $profile = $this->getEntity()->setDescription(self::FAKETEXT);
        $this->getValidationErrors($profile, 1);
    }

    public function testProfileEntityDescriptionIsInvalid(): void
    {
        $profile = $this->getEntity()->setDescription(self::FAKETEXT);
        $this->getValidationErrors($profile, 1);
    }

    private function getEntity(): Profile
    {
        $profile = new Profile();
        $profile->setFirstname("testfirstname")
            ->setLastname("testlastname")
            ->setGender("monsieur")
            ->setAddress("2 rue de test le jean moulin")
            ->setPhone("0224563429")
            ->setBirthdate(new \DateTimeImmutable())
            ->setDescription("description de test")
            ->setCreatedAt(new \DateTimeImmutable)
            ->setUpdatedAt(new \DateTimeImmutable);

        return $profile;
    }

    private function getValidationErrors(Profile $profile, int $numberOfExpectedErrors): ConstraintViolationList
    {
        $errors  = $this->validator->validate($profile);

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
