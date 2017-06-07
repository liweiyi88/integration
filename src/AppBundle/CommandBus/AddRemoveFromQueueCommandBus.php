<?php

namespace AppBundle\CommandBus;

use AppBundle\Entity\Queue;
use AppBundle\Model\Command;
use Doctrine\ORM\EntityManager;

class AddRemoveFromQueueCommandBus implements CommandBusInterface
{
    private $innerBus;
    private $entityManager;

    public function __construct(CommandBusInterface $commandBus, EntityManager $entityManager)
    {
        $this->innerBus = $commandBus;
        $this->entityManager = $entityManager;
    }

    public function handle(Command $command)
    {
        $this->innerBus->handle($command);
        $queue = $this->entityManager->getRepository(Queue::class)->findOneBy(['email' => $command->getEmail()]);
        if ($queue) {
            $this->entityManager->remove($queue);
            $this->entityManager->flush();
        }
    }
}