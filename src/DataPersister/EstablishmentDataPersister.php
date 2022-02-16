<?php

namespace App\DataPersister;

use App\Entity\Establishment;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;

final class EstablishmentDataPersister implements ContextAwareDataPersisterInterface
{

    private $_em;


    public function __construct(EntityManagerInterface $em)
    {
        $this->_em = $em;
    }
    public function supports($data, array $context = []): bool
    {
        return $data instanceof Establishment;
    }

    public function persist($data, array $context = [])
    {

        if ($data instanceof Establishment  && (($context['collection_operation_name'] ?? null) === 'post')) {
            $configuration = [
                "emailTemplate" => [],
                "equipmentConfig" => [],
                "documentConfig" => [],
                "helpDocumentConfig" => [],
            ];

            $data->setSetting($configuration);
        }
        $this->_em->persist($data);
        $this->_em->flush();
    }

    public function remove($data, array $context = [])
    {
        $this->_em->remove($data);
        $this->_em->flush();
    }
}
