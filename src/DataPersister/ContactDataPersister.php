<?php

namespace App\DataPersister;

use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use App\Email\Mailer;
use App\Entity\Contact;

final class ContactDataPersister implements ContextAwareDataPersisterInterface
{

    private $_em;
    private $_mailer;

    public function __construct(
        EntityManagerInterface $em,
        Mailer $mailer
    ) {
        $this->_em = $em;
        $this->_mailer = $mailer;
    }

    public function supports($data, array $context = []): bool
    {
        return $data instanceof Contact;
    }

    public function persist($data, array $context = [])
    {


        if ($data instanceof Contact && (($context['collection_operation_name'] ?? null) === 'post')) {

            $management = [
                "receiptConfirmation" => [
                    "state" => false,
                    "isDone" => false
                ],
                "contactAdministrationValidation" => [
                    "state" => false,
                    "supervisor" => null
                ],
                "contactAdministrationMeeting" => [
                    "state" => false,
                    "supervisor" => null,
                    "proposedDates" => [],
                    "sendEmailOk" => false,
                    "isDone" => false
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
                "contactAdministrationEquipment" => [
                    "state" => false,
                    "equipmentList" => []
                ],
                "notes" => "",
                "history" => []
            ];

            $data->setManagement($management);
            $data->setState("NR");
        }

        if ($data instanceof Contact && (($context['item_operation_name'] ?? null) === 'put')) {

            //dans le cas d'un accusé de récéption
            if (
                $data->getManagement()["receiptConfirmation"]["state"]
                && $data->getManagement()["receiptConfirmation"]["isDone"] == false
            ) {
                //envoyer le mail
                $this->_mailer->sendReceiptConfirmationMail($data);

                //reset du management
                $management = $data->getManagement();
                $management["receiptConfirmation"]["isDone"] = true;

                $data->setManagement($management);
            }

            //dans le cas d'une demande de date
            if (
                $data->getManagement()["contactAdministrationMeeting"]["proposedDates"]
                && $data->getManagement()["contactAdministrationMeeting"]["sendEmailOk"]
                && $data->getManagement()["contactAdministrationMeeting"]["isDone"] == false
            ) {
                $this->_mailer->sendMeetingMailV2($data);

                //reset du management
                $management = $data->getManagement();
                $management["contactAdministrationMeeting"]["isDone"] = true;

                $data->setManagement($management);
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
