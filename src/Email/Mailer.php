<?php

namespace App\Email;

use App\Entity\User;
use ErrorException;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Twig\Environment;
use App\Entity\Contact;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;


class Mailer
{

    private const EMAIL_SENDER = 'rh_dev_2022@ypsi.fr';
    private const FRONT_SERVEUR_ADDRESS ='https://127.0.0.1:8000';

    /**
     * @var MailerInterface
     */
    private MailerInterface $mailer;

    /**
     * @var Environment
     */
    private Environment $twig;



    public function __construct(MailerInterface $mailer, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
    }


    /**
     * @param User $user
     * @throws ErrorException
     */
    public function sendAccountConfirmationEmail(User $user): void
    {
        try {
            $body = $this->twig->render('email/default_account_confirmation.html.twig');

            $recipient = $user->getEmail();

            $subject = "Demande de confirmation de compte";

            $this->sendEmail($body, $recipient, $subject);

        } catch (ErrorException|LoaderError|RuntimeError|SyntaxError $e) {
            throw new ErrorException("Impossible d'envoyer l'email de demande de confirmation de compte. \n detail : ". $e->getMessage());
        }

    }


    /**
     * @param Contact $contact
     * @throws ErrorException
     */
    public function sendMeetingMail(Contact $contact): void
    {
        try {
        //l'email par defaut
        $body = $this->twig->render('email/default_date_confirmation.html.twig', ['contact' => $contact]);

        $emailSubject= "Demande de date de rendez vous pour entretien";

        $templateTitle = "template de date";

        //check si un template email est activé
        $emailTemplateList = $contact->getJobReference()->getEstablishment()->getSetting()['emailTemplate'];

        $email =  $this->getEmailTemplateIfActivated($emailTemplateList,$templateTitle);

        if ($email) {
            $emailSubject = $email["object"];
            $emailTemplate = $email["htmlContent"];

            //récuperation de la liste de date
            $userMeetingDate = $contact->getManagement()["contactAdministrationMeeting"]["proposedDates"];
            $lstDates = "";

            foreach ($userMeetingDate as $date) {

                $contactID = $contact->getId();
                $dateUID =  $date["uid"];
                $datePropostion = date_format(date_create($date["newDate"]), 'Y-m-d H:i:s');
                $dateTrans = "<a href=\"https://127.0.0.1:8000/validate/date/$contactID/$dateUID\"target=\"_blank\"> - $datePropostion </a> <br/>";

                $lstDates = $lstDates . " " . $dateTrans;
            }

            $emailTemplate = $this->findAndReplaceVariable("%user%",$contact->getFullName(),$emailTemplate);
            $emailTemplate = $this->findAndReplaceVariable("%date%", $lstDates,$emailTemplate);

            $body = $this->twig->render('email/base_modular_email.html.twig', ['emailTemplate' => $emailTemplate]);
        }
            $this->sendEmail($body, $contact->getEmail(), $emailSubject);

        } catch (ErrorException|LoaderError|RuntimeError|SyntaxError $e) {

            throw new ErrorException("Impossible d'envoyer l'email de demande de rendez-vous. \n detail : ". $e->getMessage());
        }
    }

    /**
     * @param Contact $contact
     * @throws ErrorException
     */
    public function sendReceiptConfirmationEmail(Contact $contact): void
    {
        try {
            $today = date("d.m.y");

            $emailSubject = "Demande de date de rendez vous pour un entretien";

            $templateTitle = "template accusé de réception";

            $emailTemplateList = $contact->getJobReference()->getEstablishment()->getSetting()['emailTemplate'];

            $body = $this->twig->render('email/default_receipt_confirmation.html.twig');

            $email = $this->getEmailTemplateIfActivated($emailTemplateList,$templateTitle);

            if ($email) {

                $emailSubject = $email["object"];
                $emailTemplate = $email["htmlContent"];

                $emailTemplate = $this->findAndReplaceVariable("%user%",$contact->getFullName(),$emailTemplate);
                $emailTemplate = $this->findAndReplaceVariable("%date%",$today,$emailTemplate);

                $body = $this->twig->render('email/base_modular_email.html.twig', ['emailTemplate' => $emailTemplate]);
            }

            $this->sendEmail($body, $contact->getEmail(), $emailSubject);

        } catch (ErrorException|LoaderError|RuntimeError|SyntaxError $e) {
            throw new ErrorException("Impossible d'envoyer l'email de l'accusé de récéption. \n detail : ". $e->getMessage());
        }

    }

    private function buildEmailMessage(string $body, string $to, string $subject): Email
    {
        return (new Email())
            ->from(self::EMAIL_SENDER)
            ->to($to)
            ->subject($subject)
            ->html($body, 'text\html');
    }

    /**
     * @param string $body
     * @param string $to
     * @param string $subject
     * @throws ErrorException
     */
    private function sendEmail(string $body, string $to, string $subject): void
    {
        try {
            $message = $this->buildEmailMessage($body,$to,$subject);
            $this->mailer->send($message);
        } catch (TransportExceptionInterface $e) {
            throw new ErrorException(" Impossible d'envoyer l'email. \n detail:".$e->getMessage());
        }
    }

    private function getEmailTemplateIfActivated( array $emailTemplateList,string  $templateTitle)
    {

        if($emailTemplateList){
            foreach ($emailTemplateList as $email){
                if (($email['title'] == $templateTitle) && $email["status"]){
                    return $email;
                }
            }
        }

        return false;
    }

    private function findAndReplaceVariable(string $variableToReplace, string $variableNewValue, mixed $template ): mixed
    {
        $newTemplate="";

        if(str_contains($template, $variableToReplace)){
           $newTemplate = str_replace($variableToReplace, $variableNewValue, $template);
        }

        return $newTemplate;
    }
}
