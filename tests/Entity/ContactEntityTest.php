<?php

namespace App\Tests\Entity;

use App\Entity\Contact;
use App\Entity\JobAdvert;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ContactEntityTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->validator = $kernel->getContainer()->get('validator');
    }

    public function testContactEntityIsValid(): void
    {
        $contact = $this->getEntity();
        $this->getValidationErrors($contact, 0);
    }

    public function testContactEntityFirstnameIsBlank(): void
    {
        $contact = $this->getEntity()->setFirstname("");
        $this->getValidationErrors($contact, 2);
    }

    public function testContactEntityFirstnameIsInvalid(): void
    {
        $contact = $this->getEntity()->setFirstname("A");
        $this->getValidationErrors($contact, 1);
    }

    public function testContactEntityLastnameIsBlank(): void
    {
        $contact = $this->getEntity()->setLastname("");
        $this->getValidationErrors($contact, 2);
    }

    public function testContactEntityLastnameIsInvalid(): void
    {
        $contact = $this->getEntity()->setLastname("A");
        $this->getValidationErrors($contact, 1);
    }

    public function testContactEntityEmailIsBlank(): void
    {
        $contact = $this->getEntity()->setEmail("");
        $this->getValidationErrors($contact, 2);
    }

    public function testContactEntityEmailIsInvalid(): void
    {
        $contact = $this->getEntity()->setEmail("aaaa");
        $this->getValidationErrors($contact, 1);
    }

    public function testContactEntitySubjectIsBlank(): void
    {
        $contact = $this->getEntity()->setSubject("");
        $this->getValidationErrors($contact, 2);
    }

    public function testContactEntitySubjectIsInvalid(): void
    {
        $contact = $this->getEntity()->setSubject("aa");
        $this->getValidationErrors($contact, 1);
    }


    private function getEntity(): Contact
    {
        $category = CategoryEntityTest::getEntity();

        $jobAdvert = new JobAdvert();

        $jobAdvert->setTitle("job de test")
            ->setPlace("test ville")
            ->setCompagny('test compagny')
            ->setContractType('CDD')
            ->setWage("1200€")
            ->setDescription("Description de test")
            ->setPublished(true)
            ->setCategory($category)
            ->setTasks(["task1", "task2", "task3"])
            ->setRequirements(["requirement1", "requirement2", "requirement3"]);

        $contact = new Contact;
        $contact->setFirstname('firstnametest')
            ->setLastname('lastnameTest')
            ->setEmail('test@testcontact.fr')
            ->setSubject('candidature au poste de testeur')
            ->setMessage('mesaage de test')
            ->setJobReference($jobAdvert);

        return $contact;
    }

    private function getValidationErrors(Contact $contact, int $numberOfExpectedErrors): ConstraintViolationList
    {
        $errors  = $this->validator->validate($contact);

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
