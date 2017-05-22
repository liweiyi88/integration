<?php
namespace AppBundle\Handler;

use AppBundle\Entity\SignUp;

class MailchimpHandler implements UserHandler
{
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
            ->setBody('Your information have been pushed to Mailchimp');

        $this->mailer->getTransport()->start();
        $this->mailer->send($emailBody);
        $this->mailer->getTransport()->stop();
    }
}
