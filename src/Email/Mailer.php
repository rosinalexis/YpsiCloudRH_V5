<?php

namespace App\Email;

use App\Entity\User;
use Twig\Environment;
use App\Entity\Contact;
use DateTime;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\VarDumper\Cloner\Data;

class Mailer
{
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

    public function sendConfirmationEmail(User $user)
    {
        $body = $this->twig->render('email/confirmation.html.twig', [
            'user' => $user
        ]);

        $message = (new Email())
            ->from('rh_dev_2022@ypsi.fr')
            ->to("ypsi.cloud.rh@gmail.com")
            ->subject('Votre compte Ypsi Cloud RH est en attente d\'activation !')
            ->html($body, 'text\html');

        $this->mailer->send($message);
    }

    public function sendReceiptConfirmationMail(Contact $contact)
    {

        $body = $this->twig->render('email/receipt_confirmation.html.twig');

        $message = (new Email())
            ->from('rh_dev_2022@ypsi.fr')
            ->to("ypsi.cloud.rh@gmail.com")
            ->subject("Accusé de réception de votre candidature")
            ->html($body, 'text\html');

        $this->mailer->send($message);
    }

    public function sendMeetingMail()
    {
        $fs = new Filesystem();
        $tmpFolder = '/tmp/';
        $fileName = 'meeting.ics';

        $original_date = "2019-03-31";

        $icsContent = "
                        BEGIN:VCALENDAR
                        VERSION:2.0
                        CALSCALE:GREGORIAN
                        METHOD:REQUEST
                        BEGIN:VEVENT
                        DTSTART:" . date('Ymd\THis', strtotime($original_date)) . "
                        DTEND:" . date('Ymd\THis', strtotime($original_date)) . "
                        DTSTAMP:" . date('Ymd\THis', strtotime($original_date)) . "
                        ORGANIZER;CN=XYZ:mailto:do-not-reply@example.com
                        UID:" . rand(5, 1500) . "
                        ATTENDEE;PARTSTAT=NEEDS-ACTION;RSVP= TRUE;CN=Sample:emailaddress@testemail.com
                        DESCRIPTION:" . "testman" . " requested Phone/Video Meeting Request
                        LOCATION: Phone/Video
                        SEQUENCE:0
                        STATUS:CONFIRMED
                        SUMMARY:Meeting has been scheduled by " . "testman" . "
                        TRANSP:OPAQUE
                        END:VEVENT
                        END:VCALENDAR";

        //creation of the file on the server
        $icfFile = $fs->dumpFile($tmpFolder . $fileName, $icsContent);

        $body = 'Test meeting...';
        $message = (new Email())
            ->from('rh_dev_2022@ypsi.fr')
            ->to("ypsi.cloud.rh@gmail.com")
            ->subject("Rendez vous entretien")
            ->text($body)
            ->attachFromPath($tmpFolder . $fileName);
        //->attachFromPath($file, null, 'text/calendar');

        $this->mailer->send($message);
        $fs->remove(array('file', $tmpFolder, $fileName));
    }


    public function sendMeetingMailV2(Contact $contact)
    {
        //l'email par defaut
        $body = $this->twig->render('email/date_confirmation.html.twig', ['contact' => $contact]);

        $emailObject = '';


        //check si un template email est activé
        $email = $contact->getJobReference()->getEstablishment()->getSetting()['emailTemplate'];
        if ($email) {
            foreach ($email as $emailTrans) {
                if (($emailTrans["title"] == "template de date") && $emailTrans["status"]) {

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

                    //traitement du message remplacement par les valeurs
                    $myEmailTemplate = $emailTrans["htmlContent"];

                    //recherche et remplacement de la variable user
                    if (str_contains($myEmailTemplate, "%user%")) {
                        $myEmailTemplate = str_replace("%user%",  $contact->getFullName(), $myEmailTemplate);
                    }

                    if (str_contains($myEmailTemplate, "%date%")) {
                        $myEmailTemplate = str_replace("%date%",  $lstDates, $myEmailTemplate);
                    }

                    //traitement de l'object de l'email
                    $emailObject = $emailTrans["object"];
                    $body = $this->twig->render('email/test_email.html.twig', ['emailTemplate' => $myEmailTemplate]);
                }
            }
        }


        $message = (new Email())
            ->from('rh_dev_2022@ypsi.fr')
            ->to("ypsi.cloud.rh@gmail.com")
            ->subject($emailObject ? $emailObject : "Demande de date de rendez vous pour entretien")
            ->html($body, 'text\html');

        $this->mailer->send($message);
    }

    public function sendMeetingMailV3(Contact $contact)
    {
        $today = date("d.m.y");
        $emailObject = '';

        $email = $contact->getJobReference()->getEstablishment()->getSetting()['emailTemplate'];

        //l'email par defaut 
        $body = $this->twig->render('email/receipt_confirmation.html.twig');

        if ($email) {
            foreach ($email as $emailTrans) {
                if (($emailTrans["title"] == "template accusé de réception") && $emailTrans["status"]) {
                    //traitement du message remplacement par les valeurs

                    $myEmailTemplate = $emailTrans["htmlContent"];

                    //recherche et remplacement de la variable user
                    if (str_contains($myEmailTemplate, "%user%")) {
                        $myEmailTemplate = str_replace("%user%",  $contact->getFullName(), $myEmailTemplate);
                    }

                    if (str_contains($myEmailTemplate, "%date%")) {
                        $myEmailTemplate = str_replace("%date%", $today, $myEmailTemplate);
                    }

                    //traitement de l'object de l'email
                    $emailObject = $emailTrans["object"];

                    $body = $this->twig->render('email/test_email.html.twig', ['emailTemplate' => $myEmailTemplate]);
                }
            }
        }
        $message = (new Email())
            ->from('rh_dev_2022@ypsi.fr')
            ->to("ypsi.cloud.rh@gmail.com")
            ->subject($emailObject ? $emailObject : "Demande de date de rendez vous pour entretien")
            ->html($body, 'text\html');

        $this->mailer->send($message);
    }
}
