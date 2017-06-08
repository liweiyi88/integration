<?php
namespace AppBundle\Model;

class ConfirmationEmail extends Command
{
    const ALIAS = 'confirmation_email';

    public function __construct(string $email, string $username)
    {
        parent::__construct($email, $username);
        $this->class = self::class;
    }
}
