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
    private $mailer;

    /**
     * @var Environment
     */
    private $twig;

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
            ->from('botgerome@ypsicloudrh.com')
            ->to("alexisbotdev@gmail.com")
            ->subject('Votre compte Ypsi Cloud RH est en attente d\'activation !')
            ->html($body, 'text\html');

        $this->mailer->send($message);
    }

    public function sendReceiptConfirmationMail(Contact $contact)
    {

        $body = $this->twig->render('email/receipt_confirmation.html.twig');

        $message = (new Email())
            ->from('yspicloudrh@ypsicloudrh.com')
            ->to("alexisbotdev@gmail.com")
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
            ->from('yspicloudrh@ypsicloudrh.com')
            ->to("alexisbotdev@gmail.com")
            ->subject("Rendez vous entretien")
            ->text($body)
            ->attachFromPath($tmpFolder . $fileName);
        //->attachFromPath($file, null, 'text/calendar');

        $this->mailer->send($message);
        $fs->remove(array('file', $tmpFolder, $fileName));
    }


    public function sendMeetingMailV2(Contact $contact)
    {
        $body = $this->twig->render('email/date_confirmation.html.twig', ['contact' => $contact]);

        $message = (new Email())
            ->from('yspicloudrh@ypsicloudrh.com')
            ->to("alexisbotdev@gmail.com")
            ->subject("Demande de date de rendez vous pour entretien")
            ->html($body, 'text\html');

        $this->mailer->send($message);
    }
}
