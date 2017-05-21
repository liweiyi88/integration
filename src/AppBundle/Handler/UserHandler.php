<?php
namespace AppBundle\Handler;

use AppBundle\Entity\SignUp;

interface UserHandler
{
    public function handle(SignUp $signUp);
}
