<?php

namespace App\Email;

use App\Entity\User;
use Twig\Environment;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

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
            ->to($user->getEmail())
            ->subject('Votre compte Ypsi Cloud RH est en attente d\'activation !')
            ->html($body, 'text\html');

        $this->mailer->send($message);
    }
}
