<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Profile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ProfileImageUploadAction extends AbstractController
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

    public function __invoke(Request $request, Profile $profile)
    {
        $uploadedFile = $request->files->get('file');

        //vérification si il exist un fichier
        if (!$uploadedFile) {
            throw new BadRequestHttpException('"file" is required');
        }

        //vérification de l'utilisateur
        /**
         * @var User $currentUser
         */
        $currentUser = $this->getUser();


        if (($currentUser == $profile->getUser()) or ($this->isGranted('ROLE_ADMIN'))) {

            //accepter les modifcations
            $profile->setFile($uploadedFile);
            $this->em->flush();

            $profile->setFile(null);

            return $profile;
        }

        throw new BadRequestHttpException('you can not make some modification on this profile.');
    }
}
