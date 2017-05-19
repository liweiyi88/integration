<?php
namespace AppBundle\Handler;

use AppBundle\Entity\User;

class Mailchimp implements UserHandler
{
    const ALIAS = 'mailchimp';
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
            ->setBody('Your information have been pushed to Mailchimp');

        $this->mailer->getTransport()->start();
        $this->mailer->send($emailBody);
        $this->mailer->getTransport()->stop();
    }
}
