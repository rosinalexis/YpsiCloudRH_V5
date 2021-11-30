<?php

namespace App\Controller;

use ApiPlatform\Core\Validator\Exception\ValidationException;
use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Entity\Image;
use App\Form\ImageType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

class UploadImageAction extends AbstractController
{
    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ValidatorValidatorInterface
     */
    private $validator;

    public function __construct(
        FormFactoryInterface $formFactory,
        EntityManagerInterface $em,
        ValidatorInterface $validator
    ) {
        $this->formFactory = $formFactory;
        $this->em = $em;
        $this->validator =  $validator;
    }

    public function __invoke(Request $request)
    {
        //création d'une nouvelle image
        $image = new Image();

        //validation du formulaire 
        $form = $this->formFactory->create(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($image);
            $this->em->flush();

            $image->setFile(null);

            return $image;
        }
        throw new ValidationException(
            $this->validator->validate($image)
        );
    }
}
