<?php

namespace App\Tests\Repository;

use App\Entity\Contact;
use App\Entity\JobAdvert;
use App\Repository\ContactRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ContactTest extends KernelTestCase
{
    private $entityManager;
    private ContactRepository $contactRepo;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->contactRepo = static::getContainer()->get(ContactRepository::class);
    }

    public function testContactRepositoryCount(): void
    {
        $contacts = $this->contactRepo->count([]);
        $this->assertEquals(0, $contacts);
    }

    public function testContactRepositoryAdd(): void
    {
        $contactTest = $this->getEntity();

        //récupération et vérification
        $contact = $this->contactRepo->findOneBy(['firstname' => 'firstnametest']);

        $this->assertSame($contactTest->getFirstname(), $contact->getFirstname());
        $this->assertSame($contactTest->getLastname(), $contact->getLastname());
        $this->assertObjectHasAttribute('id', $contact);
        $this->assertObjectHasAttribute('createdAt', $contact);
        $this->assertObjectHasAttribute('updatedAt', $contact);
    }

    public function testContactRepositoryUpdate(): void
    {
        $contactTest = $this->getEntity();

        $contactTest->setFirstname('firstnametest updated');
        $this->entityManager->flush();

        //récupération et vérification
        $contact = $this->contactRepo->findOneBy(['firstname' => 'firstnametest updated']);

        $this->assertSame($contactTest->getFirstname(), $contact->getFirstname());
        $this->assertSame($contactTest->getId(),  $contact->getId());
    }

    public function testContactRepositoryRemove(): void
    {
        $contactTest = $this->getEntity();

        //récupération et vérification
        $contact = $this->contactRepo->findOneBy(['firstname' => 'firstnametest']);
        $this->entityManager->remove($contact);
        $this->entityManager->flush();

        //vérification
        $this->assertNull($this->contactRepo->findOneBy(['firstname' => 'firstnametest']));
    }


    private function getEntity(): Contact
    {
        $category = \App\Tests\Entity\CategoryEntityTest::getEntity();
        $this->entityManager->persist($category);

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

        $this->entityManager->persist($jobAdvert);

        $contact = new Contact;
        $contact->setFirstname('firstnametest')
            ->setLastname('lastnameTest')
            ->setEmail('test@testcontact.fr')
            ->setSubject('candidature au poste de testeur')
            ->setMessage('mesaage de test')
            ->setJobReference($jobAdvert);

        $this->entityManager->persist($contact);
        $this->entityManager->flush();

        return $contact;
    }


    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
