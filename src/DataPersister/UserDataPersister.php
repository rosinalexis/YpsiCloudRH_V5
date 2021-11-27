<?php

namespace App\DataPersister;

use App\Entity\User;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

final class UserDataPersister implements ContextAwareDataPersisterInterface
{

    private $_em;
    private $_passwordHasher;

    public function __construct(
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher

    ) {
        $this->_em = $em;
        $this->_passwordHasher = $passwordHasher;
    }


    public function supports($data, array $context = []): bool
    {
        return $data instanceof User;
    }


    public function persist($data, array $context = [])
    {
        if ($data instanceof User && (($context['collection_operation_name'] ?? null) === 'post')) {

            $data->setPassword(
                $this->_passwordHasher->hashPassword(
                    $data,
                    $data->getPlainPassword()
                )
            );

            $data->eraseCredentials();
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
