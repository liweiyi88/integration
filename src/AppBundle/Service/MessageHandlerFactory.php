<?php
namespace AppBundle\Service;

use AppBundle\Messaging\ConfirmationEmail;
use AppBundle\Messaging\Mailchimp;
use AppBundle\Messaging\MessageHandler;

class MessageHandlerFactory
{
    private $confirmationEmail;
    private $mailchimp;

    public function __construct(ConfirmationEmail $confirmationEmail, Mailchimp $mailchimp)
    {
        $this->confirmationEmail = $confirmationEmail;
        $this->mailchimp = $mailchimp;
    }

    /**
     * @param string $handler
     * @return MessageHandler
     */
    public function get($handler)
    {
        switch ($handler) {
            case ConfirmationEmail::ALIAS:
                return $this->confirmationEmail;
            case Mailchimp::ALIAS:
                return $this->mailchimp;
        }

        throw new \InvalidArgumentException('Unsupported Processor');
    }
}
