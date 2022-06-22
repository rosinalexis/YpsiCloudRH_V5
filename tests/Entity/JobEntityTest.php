<?php

namespace App\Tests\Entity;

use App\Entity\Job;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class JobEntityTest extends KernelTestCase
{

    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->validator = $kernel->getContainer()->get('validator');
    }

    public function testJobEntityIsValid(): void
    {
        $job = $this->getEntity();
        $this->getValidationErrors($job, 0);
    }

    public function testJobEntityTitleIsBlank(): void
    {
        $job = $this->getEntity()->setTitle("");
        $this->getValidationErrors($job, 2);
    }

    public function testJobEntityTitleIsInvalid(): void
    {
        $job = $this->getEntity()->setTitle("AZ");
        $this->getValidationErrors($job, 1);
    }

    public function testJobEntityDescriptionIsBlack(): void
    {
        $job = $this->getEntity()->setDescription("");
        $this->getValidationErrors($job, 0);
    }


    private function getEntity(): Job
    {
        $job = new Job();
        $job->setTitle("testJob")
            ->setDescription("testdescriptionjob")
            ->setCreatedAt(new DateTimeImmutable)
            ->setUpdatedAt(new DateTimeImmutable);

        return $job;
    }

    private function getValidationErrors(Job $job, int $numberOfExpectedErrors): ConstraintViolationList
    {
        $errors  = $this->validator->validate($job);

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
