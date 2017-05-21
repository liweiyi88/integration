<?php

use AppBundle\Entity\Command;

interface CommandBusInterface
{
    public function handle(Command $command);
}