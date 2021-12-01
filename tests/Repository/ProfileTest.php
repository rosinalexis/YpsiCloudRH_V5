<?php

namespace App\Tests\Repository;

use App\Entity\Profile;
use App\Repository\ProfileRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProfileTest extends KernelTestCase
{
    private $entityManager;

    private ProfileRepository $profileRepo;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->profileRepo = static::getContainer()->get(ProfileRepository::class);
    }

    public function testProfileRepositoryCount(): void
    {
        // récuperation des profiles
        $profiles = $this->profileRepo->count([]);
        $this->assertEquals(5, $profiles);
    }

    public function testProfileRespositoryAdd(): void
    {

        $profileTest = $this->getEntity();
        $this->entityManager->persist($profileTest);
        $this->entityManager->flush();


        //récuperation et vérification 
        $profile = $this->profileRepo->findOneBy(['firstname' => 'testfirstname']);
        $this->assertSame("testfirstname", $profile->getFirstname());
        $this->assertSame("testlastname", $profile->getLastname());
        $this->assertSame(strtolower("2 rue de Test le Jean Moulin 89000"), $profile->getAddress());
        $this->assertSame("monsieur", $profile->getGender());
        $this->assertSame("0224563429", $profile->getPhone());
        $this->assertObjectHasAttribute('id', $profile);
        $this->assertObjectHasAttribute('createdAt', $profile);
        $this->assertObjectHasAttribute('updatedAt', $profile);
    }

    public function testProfileRepositoryUpdate(): void
    {
        //création
        $profileTest = $this->getEntity();

        $this->entityManager->persist($profileTest);
        $this->entityManager->flush();

        //identification et modification
        $profileTest->setFirstname("testfirstname Update");
        $this->entityManager->flush();

        //récuperation et vérification 
        $profile = $this->profileRepo->findOneBy(['firstname' => 'testfirstname update']);
        $this->assertSame($profileTest->getFirstname(), $profile->getFirstname());
        $this->assertSame($profileTest->getId(), $profile->getId());
    }

    public function testProfileRemove(): void
    {
        //création
        $profileTest = $this->getEntity();

        $this->entityManager->persist($profileTest);
        $this->entityManager->flush();

        //récuperation et suppression 
        $profile = $this->profileRepo->findOneBy(['firstname' => 'testfirstname']);
        $this->entityManager->remove($profile);
        $this->entityManager->flush();

        //vérification
        $this->assertNull($this->profileRepo->findOneBy(['firstname' => 'testfirstname']));
    }


    private function getEntity(): Profile
    {
        $profile = new Profile;
        $profile->setFirstname("testfirstname")
            ->setLastname("testlastname")
            ->setGender("Monsieur")
            ->setAddress("2 rue de Test le Jean Moulin 89000")
            ->setPhone("0224563429")
            ->setBirthdate(new \DateTimeImmutable())
            ->setDescription("description de test");

        return $profile;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
