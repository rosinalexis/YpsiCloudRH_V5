<?php

namespace App\Tests\Repository;

use App\Entity\Job;
use App\Repository\JobRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class JobTest extends KernelTestCase
{
    private $entityManager;
    private JobRepository $jobRepo;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->jobRepo = static::getContainer()->get(JobRepository::class);
    }

    public function testJobRepositoryCount(): void
    {
        $jobs = $this->jobRepo->count([]);
        $this->assertEquals(5, $jobs);
    }

    public function testJobRepositoryAdd(): void
    {
        $jobTest = $this->getEntity();

        //récupetatio et vérification 
        $job = $this->jobRepo->findOneBy(['title' => 'testjobtitle']);
        $this->assertSame($jobTest->getTitle(), $job->getTitle());
        $this->assertSame($jobTest->getDescription(), $job->getDescription());
        $this->assertObjectHasAttribute('id', $job);
        $this->assertObjectHasAttribute('createdAt', $job);
        $this->assertObjectHasAttribute('updatedAt', $job);
    }

    public function testJobRepositoryUpdate(): void
    {
        //création 
        $jobTest = $this->getEntity();

        $jobTest->setTitle("testJobTitle  Update");
        $this->entityManager->flush();

        $job = $this->jobRepo->findOneBy(['title' => 'testjobtitle  update']);
        $this->assertSame($jobTest->getDescription(), $job->getDescription());
        $this->assertSame($jobTest->getId(),  $job->getId());
    }

    public function testJobRepositoryRemove(): void
    {
        //création 
        $jobTest = $this->getEntity();

        //récuperation et suppression
        $job = $this->jobRepo->findOneBy(['title' => 'testjobtitle']);
        $this->entityManager->remove($job);
        $this->entityManager->flush();

        //vérification
        $this->assertNull($this->jobRepo->findOneBy(['title' => 'testjobtitle']));
    }

    private function getEntity(): Job
    {
        $job = new Job;
        $job->setTitle("testJobTitle")
            ->setDescription("testJobDescription");

        $this->entityManager->persist($job);
        $this->entityManager->flush();

        return $job;
    }


    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
