<?php
namespace AppBundle\Handler;

use AppBundle\Entity\SignUp;

class ConfirmationEmail implements UserHandler
{
    const ALIAS = 'confirmation_email';
    private $mailer;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function handle(SignUp $signUp)
    {
        $emailBody = \Swift_Message::newInstance()
            ->setSubject('Email Confirmation')
            ->setFrom('admin@escapestring.com')
            ->setTo($signUp->getEmail())
            ->setBody('Your account has been confirmed');

        $this->mailer->getTransport()->start();
        $this->mailer->send($emailBody);
        $this->mailer->getTransport()->stop();
    }
}
