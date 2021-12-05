<?php

namespace App\Tests\Repository;

use App\Entity\JobAdvert;
use App\Repository\JobAdvertRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class JobAdvertTest extends KernelTestCase
{
    private $entityManager;
    private JobAdvertRepository $jobAdvertRepo;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->jobAdvertRepo = static::getContainer()->get(JobAdvertRepository::class);
    }

    public function testJobAdvertRepositoryCount(): void
    {
        $jobAdvert = $this->jobAdvertRepo->count([]);
        $this->assertEquals(5, $jobAdvert);
    }

    public function testJobAdvertRepositoryAdd(): void
    {
        $jobAdvertTest = $this->getEntity();

        //récupération et vérification
        $jobAdvert = $this->jobAdvertRepo->findOneBy(['title' => 'job de test']);

        $this->assertSame($jobAdvertTest->getTitle(), $jobAdvert->getTitle());
        $this->assertSame($jobAdvertTest->getDescription(), $jobAdvert->getDescription());
        $this->assertObjectHasAttribute('id', $jobAdvert);
        $this->assertObjectHasAttribute('createdAt', $jobAdvert);
        $this->assertObjectHasAttribute('updatedAt', $jobAdvert);
    }

    public function testJobAdvertRepositoryUpdate(): void
    {
        $jobAdvertTest = $this->getEntity();

        //récupération et vérification
        $jobAdvert = $this->jobAdvertRepo->findOneBy(['title' => 'job de test']);
        $jobAdvert->setTitle('testjobtitle  update');
        $this->entityManager->flush();

        $job = $this->jobAdvertRepo->findOneBy(['title' => 'testjobtitle  update']);
        $this->assertSame('testjobtitle  update', $job->getTitle());
        $this->assertSame($jobAdvertTest->getDescription(), $job->getDescription());
        $this->assertSame($jobAdvertTest->getId(),  $job->getId());
    }

    public function testJobAdvertRepositoryRemove(): void
    {
        $jobAdvertTest = $this->getEntity();

        //récupération et vérification
        $jobAdvert = $this->jobAdvertRepo->findOneBy(['title' => 'job de test']);
        $this->entityManager->remove($jobAdvert);
        $this->entityManager->flush();

        //vérification
        $this->assertNull($this->jobAdvertRepo->findOneBy(['title' => 'job de test']));
    }



    private function getEntity(): JobAdvert
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
        $this->entityManager->flush();

        return $jobAdvert;
    }


    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
