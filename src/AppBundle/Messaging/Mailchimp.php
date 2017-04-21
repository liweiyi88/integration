<?php
namespace AppBundle\Messaging;

class Mailchimp implements MessageHandler
{
    const ALIAS = 'mailchimp';
    private $mailer;

    public function __construct(\Swift_Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public function handle($message = null)
    {
        if ($message != null) {
            $messageJson = json_decode($message['Body'], true);
            $user = json_decode($messageJson['Message'], true);

            $emailBody = \Swift_Message::newInstance()
                ->setSubject('Email Confirmation')
                ->setFrom('admin@escapestring.com')
                ->setTo($user['email'])
                ->setBody('Your information have been pushed to Mailchimp');

            $this->mailer->getTransport()->start();
            $this->mailer->send($emailBody);
            $this->mailer->getTransport()->stop();
        }
    }
}
