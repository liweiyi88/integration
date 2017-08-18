<?php

namespace AppBundle\SignUp;


use AppBundle\Entity\SignUp;
use Symfony\Component\EventDispatcher\Event;

class SignUpEvent extends Event
{
    private $signUp;

    public function __construct(SignUp $signUp)
    {
        $this->signUp = $signUp;
    }

    public function getSignUp(): SignUp
    {
        return $this->signUp;
    }
}