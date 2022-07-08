<?php

namespace App\DataPersister;

use App\Entity\Establishment;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;

final class EstablishmentDataPersister implements ContextAwareDataPersisterInterface
{

    private EntityManagerInterface $_em;


    public function __construct(EntityManagerInterface $em)
    {
        $this->_em = $em;
    }
    public function supports($data, array $context = []): bool
    {
        return $data instanceof Establishment;
    }

    public function persist($data, array $context = []) :void
    {

        if ($data instanceof Establishment  && (($context['collection_operation_name'] ?? null) === 'post')) {
            $configuration = [
                "emailTemplate" => [
                    [
                        "title" => "template accusé de réception",
                        "status" => false,
                        "object" => " accusé de réception test",
                        "content" => [
                            "ops" => []
                        ],
                        "htmlContent" => "<p>Bonjour  %user%, </p> <br/> <p>je suis la version 1</p> <br/> <p>cordialement</p>"
                    ],
                    [
                        "title" => "template de date",
                        "status" => false,
                        "object" => "template accusé de réception date",
                        "content" => [
                            "ops" => []
                        ],
                        "htmlContent" => "<p>Bonjour  %user%, </p> <br/> <p>je suis la version 2</p> <br/> <p>cordialement</p>"
                    ]
                ],
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
