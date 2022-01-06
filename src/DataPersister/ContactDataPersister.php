<?php

namespace App\DataPersister;

use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\Contact;

final class ContactDataPersister implements ContextAwareDataPersisterInterface
{

    private $_em;

    public function __construct(
        EntityManagerInterface $em,
    ) {
        $this->_em = $em;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Contact;
    }

    public function persist($data, array $context = [])
    {

        if ($data instanceof Contact && (($context['collection_operation_name'] ?? null) === 'post')) {

            $management = [
                "contactAdministrationValidation" => [
                    "state" => false,
                    "supervisor" => null
                ],
                "contactAdministrationMeeting" => [
                    "state" => false,
                    "supervisor" => null
                ],
                "contactAdministrationHelp" => [
                    "state" => false,
                    "helpList" => []
                ],
                "contactAdministrationDocument" => [
                    "state" => false,
                    "documentList" => []
                ],
                "contactAdministrationContract" => [
                    "state" => false,
                ],
                "contactAdministrationEquipement" => [
                    "state" => false,
                    "equipementList" => []
                ]
            ];

            $data->setManagement($management);
            $data->setState("NR");
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
