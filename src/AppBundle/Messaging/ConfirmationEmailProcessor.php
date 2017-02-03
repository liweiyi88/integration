<?php
namespace AppBundle\Messaging;

class ConfirmationEmailProcessor implements MessageProcessInterface
{
    private $mailer;

    public function __construct($mailer)
    {
        $this->mailer = $mailer;
    }

    public function process($message)
    {
        if ($message != null) {
            $messageJson = json_decode($message['Body'], true);
            $user = json_decode($messageJson['Message'], true);

            $emailBody = \Swift_Message::newInstance()
                ->setSubject('Email Confirmation')
                ->setFrom('weiyi.li713@gmail.com')
                ->setTo($user['email'])
                ->setBody('Your account has been confirmed');

            $this->mailer->send($emailBody);
        }
    }
}