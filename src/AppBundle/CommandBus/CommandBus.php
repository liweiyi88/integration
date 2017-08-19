<?php

namespace AppBundle\CommandBus;

use AppBundle\Model\Command;
use Symfony\Component\DependencyInjection\ServiceLocator;

class CommandBus implements CommandBusInterface
{
    private $handlerLocator;

    public function __construct(ServiceLocator $locator)
    {
        $this->handlerLocator = $locator;
    }

    public function handle(Command $command)
    {
        $commandClass = get_class($command);

        if (!$this->handlerLocator->has($commandClass)) {
            return;
        }

        $handler = $this->handlerLocator->get($commandClass);

        return $handler->handle($command);
    }
}
