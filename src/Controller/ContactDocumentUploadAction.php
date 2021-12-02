<?php

namespace App\Controller;

use App\Entity\Contact;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ContactDocumentUploadAction extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    private $em;


    public function __construct(
        EntityManagerInterface $em,
    ) {
        $this->em = $em;
    }


    public function __invoke(Request $request, Contact $contact)
    {
        $cvUploadFile = $request->files->get('cvFile');
        $coverLetterUploadFile = $request->files->get('coverLetterFile');

        if (!$contact) {
            throw new BadRequestHttpException('Could not found the contact informations.');
        }
        //vérification si il exist un fichier
        if (!($cvUploadFile && $coverLetterUploadFile)) {
            throw new BadRequestHttpException('"cv" and "cover letter" are required');
        }


        $contact->setCvFile($cvUploadFile)
            ->setCoverLetterFile($coverLetterUploadFile);

        $this->em->flush();

        $contact->setCvFile(null);
        $contact->setCoverLetterFile(null);

        return $contact;
    }
}
