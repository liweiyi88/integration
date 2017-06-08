<?php

namespace AppBundle\Model;

class Mailchimp extends Command
{
    const ALIAS = 'mailchimp';

    public function __construct(string $email, string $username)
    {
        parent::__construct($email, $username);
        $this->class = self::class;
    }
}
