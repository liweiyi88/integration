<?php
namespace AppBundle\Handler;

use AppBundle\Entity\User;

class ConfirmationEmail implements UserHandler
{
    const ALIAS = 'confirmation_email';
    private $mailer;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function handle(User $user)
    {
        $emailBody = \Swift_Message::newInstance()
            ->setSubject('Email Confirmation')
            ->setFrom('admin@escapestring.com')
            ->setTo($user->getEmail())
            ->setBody('Your account has been confirmed');

        $this->mailer->getTransport()->start();
        $this->mailer->send($emailBody);
        $this->mailer->getTransport()->stop();
    }
}
