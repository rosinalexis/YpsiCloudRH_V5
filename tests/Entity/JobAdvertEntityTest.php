<?php

namespace App\Tests\Entity;

use App\Entity\JobAdvert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class JobAdvertEntityTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->validator = $kernel->getContainer()->get('validator');
    }

    public function testJobAdvertEntityIsValid(): void
    {
        $jobAdvert = $this->getEntity();
        $this->getValidationErrors($jobAdvert, 0);
    }

    public function testJobAdvertTitleIsBlank(): void
    {
        $jobAdvert = $this->getEntity()->setTitle(" ");
        $this->getValidationErrors($jobAdvert, 1);
    }

    public function testJobAdvertTitleIsInvalid(): void
    {
        $jobAdvert = $this->getEntity()->setTitle("AA");
        $this->getValidationErrors($jobAdvert, 1);
    }

    public function testJobAdvertPlaceIsBlank(): void
    {
        $jobAdvert = $this->getEntity()->setPlace("");
        $this->getValidationErrors($jobAdvert, 2);
    }

    public function testJobAdvertPlaceIsInvalid(): void
    {
        $jobAdvert = $this->getEntity()->setPlace("AA");
        $this->getValidationErrors($jobAdvert, 1);
    }


    public function testJobAdvertCompagnyIsBlank(): void
    {
        $jobAdvert = $this->getEntity()->setCompagny("");
        $this->getValidationErrors($jobAdvert, 2);
    }

    public function testJobAdvertCompagnyIsInvalid(): void
    {
        $jobAdvert = $this->getEntity()->setCompagny("AA");
        $this->getValidationErrors($jobAdvert, 1);
    }


    public function testJobAdvertContractTypeIsBlank(): void
    {
        $jobAdvert = $this->getEntity()->setContractType(" ");
        $this->getValidationErrors($jobAdvert, 1);
    }

    public function testJobAdvertContractTypeIsInvalid(): void
    {
        $jobAdvert = $this->getEntity()->setContractType("11");
        $this->getValidationErrors($jobAdvert, 1);
    }


    public function testJobAdvertContractWageIsBlank(): void
    {
        $jobAdvert = $this->getEntity()->setWage("");
        $this->getValidationErrors($jobAdvert, 1);
    }

    public function testJobAdvertContractWageIsInvalid(): void
    {
        $jobAdvert = $this->getEntity()->setWage("AA");
        $this->getValidationErrors($jobAdvert, 1);
    }

    public function testJobAdvertContractDescriptionIsBlank(): void
    {
        $jobAdvert = $this->getEntity()->setDescription("");
        $this->getValidationErrors($jobAdvert, 0);
    }


    private function getEntity(): JobAdvert
    {
        $jobAdvert = new JobAdvert();

        $jobAdvert->setTitle("job de test")
            ->setPlace("test ville")
            ->setCompagny('test compagny')
            ->setContractType('CDD')
            ->setWage("1200€")
            ->setDescription("Description de test")
            ->setPublished(true)
            ->setCategory(\App\Tests\Entity\CategoryEntityTest::getEntity())
            ->setTasks(["task1", "task2", "task3"])
            ->setRequirements(["requirement1", "requirement2", "requirement3"]);

        return $jobAdvert;
    }


    private function getValidationErrors(JobAdvert $jobAdvert, int $numberOfExpectedErrors): void
    {
        $errors  = $this->validator->validate($jobAdvert);

        $messages = [];
        /**
         * @var ConstraintViolation $error
         */
        foreach ($errors as $error) {
            $messages[] = $error->getPropertyPath() . ' => ' . $error->getMessage();
        }

        $this->assertCount($numberOfExpectedErrors, $errors, implode(', ', $messages));

    }


}
