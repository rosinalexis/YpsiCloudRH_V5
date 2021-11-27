<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\Profile;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

final class ProfileDataPersister implements ContextAwareDataPersisterInterface
{
    private $_em;
    private $_security;

    public function __construct(Security $security, EntityManagerInterface $em)
    {
        $this->_em = $em;
        $this->_security = $security;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Profile;
    }

    public function persist($data, array $context = [])
    {
        if ($data instanceof Profile && (($context['collection_operation_name'] ?? null) === 'post')) {
            /**
             * @var User $user
             */
            $user = $this->_security->getUser();
            $data->setUser($user);
        }

        //enregistrement des données
        $this->_em->persist($data);
        $this->_em->flush();
    }


    public function remove($data, array $context = [])
    {
        //je remets le profil à null
        $data->getUser()->setProfile(NULL);


        //enregistement des modifications
        $this->_em->remove($data);
        $this->_em->flush();
    }
}
