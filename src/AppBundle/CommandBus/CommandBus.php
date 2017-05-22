<?php

namespace AppBundle\CommandBus;

use AppBundle\Entity\Command;

class CommandBus implements CommandBusInterface
{
    private $handlerLocator;

    public function handle(Command $command)
    {
        $commandClass = get_class($command);

        if (!$this->handlerLocator->has($commandClass)) {
            return;
        }

        // get the service from the service locator (and instantiate it)
        $handler = $this->handlerLocator->get($commandClass);

        return $handler->handle($command);
    }
}