<?php

namespace AppBundle\CommandBus;

use AppBundle\Model\Command;

interface CommandBusInterface
{
    public function handle(Command $command);
}