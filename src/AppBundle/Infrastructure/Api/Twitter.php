<?php

namespace AppBundle\Infrastructure\Api;

use AppBundle\Entity\SignUp;

class Twitter
{
    public function push(SignUp $signUp)
    {
        echo 'pushed';
    }
}