<?php

namespace AppBundle\Handler;

use AppBundle\Model\Command;

interface Handler
{
    public function handle(Command $command);
}
