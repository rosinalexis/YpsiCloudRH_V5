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

    private function buildEmailMessage(string $body, string $recipient, string $subject): Email
    {
        return (new Email())
            ->from(self::EMAIL_SENDER)
            ->to($recipient)
            ->subject($subject)
            ->html($body, 'text\html');
    }

    /**
     * @param string $body
     * @param string $recipient
     * @param string $subject
     * @throws ErrorException
     */
    private function sendEmail(string $body, string $recipient, string $subject): void
    {
        try {
            $message = $this->buildEmailMessage($body,$recipient,$subject);
            $this->mailer->send($message);
        } catch (TransportExceptionInterface $e) {
            throw new ErrorException(" Impossible d'envoyer l'email. \n detail:".$e->getMessage());
        }
    }

    /**
     * @param User $user
     * @throws ErrorException
     */
    public function sendConfirmationEmail(User $user): void
    {
        try {
            $body = $this->twig->render('email/confirmation.html.twig', [
                'user' => $user
            ]);

            $recipient = $user->getEmail();

            $subject = 'Votre compte Ypsi Cloud RH est en attente d\'activation !';

            $this->sendEmail($body, $recipient, $subject);

        } catch (ErrorException|LoaderError|RuntimeError|SyntaxError $e) {
            throw new ErrorException("Impossible d'envoyer l'email de confirmation ");
        }

    }

    /**
     * @param Contact $contact
     * @throws ErrorException
     */
    public function sendReceiptConfirmationMail(Contact $contact): void
    {
        try {
            $body = $this->twig->render('email/receipt_confirmation.html.twig');

            $recipient = $contact->getEmail();

            $subject = "Accusé de réception de votre candidature";

            $this->sendEmail($body, $recipient, $subject);

        } catch (ErrorException|LoaderError|RuntimeError|SyntaxError $e) {
            throw new ErrorException("Impossible d'envoyer l'email de l'accusé de récéption. \n detail : ". $e->getMessage());
        }

    }

    /**
     * @throws ErrorException
     */
    public function sendMeetingMailV2(Contact $contact)
    {
        try {
        //l'email par defaut
        $body = $this->twig->render('email/date_confirmation.html.twig', ['contact' => $contact]);

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
     * @throws ErrorException
     */
    public function sendMeetingMailV3(Contact $contact): void
    {
        try {
            $today = date("d.m.y");

            $emailSubject = "Demande de date de rendez vous pour un entretien";

            $templateTitle = "template accusé de réception";

            $emailTemplateList = $contact->getJobReference()->getEstablishment()->getSetting()['emailTemplate'];

            $body = $this->twig->render('email/receipt_confirmation.html.twig');

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

    private function getEmailTemplateIfActivated( array $emailTemplateList,string  $templateTitle)
    {

        if($emailTemplateList){
            foreach ($emailTemplateList as $email){
                if (($email['title'] == $templateTitle) && $email["status"]){
                   // $emailSubject = $email["object"];
                    // $emailHtmlContent  = $email["htmlContent"];
                    return $email;
                }
            }
        }
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
