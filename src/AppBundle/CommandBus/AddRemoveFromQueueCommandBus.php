<?php

namespace AppBundle\CommandBus;

use AppBundle\Model\Command;
use AppBundle\Repository\QueueRepository;

class AddRemoveFromQueueCommandBus implements CommandBusInterface
{
    private $innerBus;
    private $queueRepository;

    public function __construct(CommandBusInterface $commandBus, QueueRepository $queueRepository)
    {
        $this->innerBus = $commandBus;
        $this->queueRepository = $queueRepository;
    }

    public function handle(Command $command)
    {
        $this->innerBus->handle($command);
        $queue = $this->queueRepository->findBy(['email' => $command->getEmail()]);
        $this->queueRepository->delete($queue);
    }
}