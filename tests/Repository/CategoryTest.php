<?php

namespace App\Tests\Repository;

use App\Entity\Category;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class CategoryTest extends KernelTestCase
{
    private $entityManager;
    private CategoryRepository $categoryRepo;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->entityManager = $kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->categoryRepo = static::getContainer()->get(categoryRepository::class);
    }

    public function testCategoryRepositoryCount(): void
    {
        $categories = $this->categoryRepo->count([]);
        $this->assertEquals(5, $categories);
    }

    public function testCategoryRepositoryAdd(): void
    {
        $categoryTest = $this->getEntity();

        //récupération et vérification
        $category = $this->categoryRepo->findOneBy(['title' => 'testCategoryTitle']);
        $this->assertSame($categoryTest->getTitle(), $category->getTitle());
        $this->assertSame($categoryTest->getDescription(), $category->getDescription());
        $this->assertObjectHasAttribute('id', $category);
        $this->assertObjectHasAttribute('createdAt', $category);
        $this->assertObjectHasAttribute('updatedAt', $category);
    }

    public function testCategoryRepositoryUpdate(): void
    {
        //création
        $categoryTest = $this->getEntity();

        $categoryTest->setTitle("testCateogryTitle  Update");
        $this->entityManager->flush();

        //récupération et vérification
        $category = $this->categoryRepo->findOneBy(['title' => 'testCateogryTitle  Update']);

        $this->assertSame($categoryTest->getDescription(), $category->getDescription());
        $this->assertSame($categoryTest->getId(),  $category->getId());
    }

    public function testCategoryRepositoryRemove(): void
    {
        //création
        $categoryTest = $this->getEntity();

        //récupération et vérification
        $category = $this->categoryRepo->findOneBy(['title' => 'testCategoryTitle']);
        $this->entityManager->remove($category);
        $this->entityManager->flush();

        //vérification 
        $this->assertNull($this->categoryRepo->findOneBy(['title' => 'testCategoryTitle']));
    }

    private function getEntity(): Category
    {
        $category = new Category;
        $category->setTitle("testCategoryTitle")
            ->setDescription("testCategoryDescription");

        $this->entityManager->persist($category);
        $this->entityManager->flush();

        return $category;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->close();
        $this->entityManager = null;
    }
}
