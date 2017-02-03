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
            $messageJson = json_decode($message, true);
            $user = json_decode($messageJson['Message'], true);

            $emailBody = \Swift_Message::newInstance()
                ->setSubject('Email Confirmation')
                ->setFrom('weiyi.li713@gmail.com')
                ->setTo($user['email'])
                ->setBody('Your application has been confirmed! Thanks for the registration');

            ;

            $this->mailer->send($emailBody);
        }
    }
}