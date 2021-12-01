<?php

namespace App\Tests\Entity;

use App\Entity\Category;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class CategoryEntityTest extends KernelTestCase
{
    private ValidatorInterface $validator;

    protected function setUp(): void
    {
        $kernel = self::bootKernel();
        $this->validator = $kernel->getContainer()->get('validator');
    }

    public function testCategoryEntityIsValid(): void
    {
        $category = $this->getEntity();
        $this->getValidationErrors($category, 0);
    }

    public function testCategoryEntityTitleIsBlank(): void
    {
        $category = $this->getEntity()->setTitle("");
        $this->getValidationErrors($category, 2);
    }

    public function testCategoryEntityTitleIsInvalid(): void
    {
        $category = $this->getEntity()->setTitle("AZ");
        $this->getValidationErrors($category, 1);
    }

    public function testCategoryEntityDescriptionIsBlank(): void
    {
        $category = $this->getEntity()->setDescription("");
        $this->getValidationErrors($category, 0);
    }


    private function getEntity(): Category
    {
        $category = new Category();
        $category->setTitle("testJob")
            ->setDescription("testdescriptionjob")
            ->setCreatedAt(new \DateTimeImmutable)
            ->setUpdatedAt(new \DateTimeImmutable);

        return $category;
    }

    private function getValidationErrors(Category $category, int $numberOfExpectedErrors): ConstraintViolationList
    {
        $errors  = $this->validator->validate($category);

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
