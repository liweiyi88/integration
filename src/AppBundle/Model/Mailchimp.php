<?php

namespace AppBundle\Model;

class Mailchimp extends Command
{
    const ALIAS = 'mailchimp';

    private $username;

    public function __construct()
    {
        $this->class = self::class;
    }

    /**
     * @return mixed
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }
}