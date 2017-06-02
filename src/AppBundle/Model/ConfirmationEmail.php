<?php
namespace AppBundle\Model;

class ConfirmationEmail extends Command
{
    const ALIAS = 'confirmation_email';

    private $username;

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