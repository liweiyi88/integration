<?php
namespace AppBundle\Model;

class ConfirmationEmail extends Command
{
    const ALIAS = 'confirmation_email';

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
