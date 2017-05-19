<?php
namespace AppBundle\Handler;

use AppBundle\Entity\User;

interface UserHandler
{
    public function handle(User $user);
}
