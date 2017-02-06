<?php
namespace AppBundle\Messaging;

class MailchimpProcessor implements MessageProcessInterface
{
    private $mailer;

    public function __construct($mailer)
    {
        $this->mailer = $mailer;
    }

    public function process($message = null)
    {
        if ($message != null) {
            $messageJson = json_decode($message['Body'], true);
            $user = json_decode($messageJson['Message'], true);

            $emailBody = \Swift_Message::newInstance()
                ->setSubject('Email Confirmation')
                ->setFrom('mailerweiyi@gmail.com')
                ->setTo($user['email'])
                ->setBody('Your information have been pushed to Mailchimp');

            $this->mailer->send($emailBody);
        }
    }
}