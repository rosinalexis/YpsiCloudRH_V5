<?php

namespace App\DataPersister;

use App\Entity\User;
use App\Email\Mailer;
use App\Security\TokenGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;



final class UserDataPersister implements ContextAwareDataPersisterInterface
{

    private $_em;
    private $_passwordHasher;
    private $_tokeGenerator;
    private $_mailer;

    public function __construct(
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
        TokenGenerator $tokenGenerator,
        Mailer $mailer

    ) {
        $this->_em = $em;
        $this->_passwordHasher = $passwordHasher;
        $this->_tokeGenerator = $tokenGenerator;
        $this->_mailer = $mailer;
    }


    public function supports($data, array $context = []): bool
    {
        return $data instanceof User;
    }


    public function persist($data, array $context = [])
    {

        if ($data instanceof User && (($context['collection_operation_name'] ?? null) === 'post')) {

            // hash du mot de passe de l'utilisateur
            $data->setPassword(
                $this->_passwordHasher->hashPassword(
                    $data,
                    $data->getPlainPassword()
                )
            );

            // reset du planPassword 
            $data->eraseCredentials();

            // désactiver le compte par default
            $data->setIsActivated(false);

            //Creation d'un token pour la connexion
            $data->setConfirmationToken(
                $this->_tokeGenerator->getRandomeSecureToken()
            );

            $this->_mailer->sendConfirmationEmail($data);
        }

        if ($data instanceof User && (($context['item_operation_name'] ?? null) === 'put')) {

            // check si le compte a été activé par l'utilisateur 
            if ($data->getConfirmationToken()) {
                throw new BadRequestHttpException("This user has not yet activated his account.");
            }
        }

        //enregistrement des données 
        $this->_em->persist($data);
        $this->_em->flush();
    }


    public function remove($data, array $context = [])
    {
        $this->_em->remove($data);
        $this->_em->flush();
    }
}
