<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Entity\JobAdvert;
use Doctrine\ORM\EntityManagerInterface;

final class JobAdvertDataPersister implements ContextAwareDataPersisterInterface
{

    private EntityManagerInterface $_em;


    public function __construct(EntityManagerInterface $em)
    {
        $this->_em = $em;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof JobAdvert;
    }

    public function persist($data, array $context = [])
    {
        if($data instanceof  JobAdvert && (( $context['collection_operation_name'] ?? null)=== 'post'))
        {
            try {
                $data->setReference(bin2hex(random_bytes(16)));
            } catch (\Exception $e) {
                $data->setReference("pas de reference");
            }
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