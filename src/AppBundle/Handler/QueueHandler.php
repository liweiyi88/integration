<?php

namespace AppBundle\Handler;

use AppBundle\Model\Command;
use AppBundle\Entity\User;
use AppBundle\Repository\QueueRepository;

class QueueHandler implements Handler
{
    private $queueRepository;

    public function __construct(QueueRepository $queueRepository)
    {
        $this->queueRepository = $queueRepository;
    }

    public function handle(Command $command)
    {
        $queue = $this->queueRepository->findBy(['email' => $command->getEmail()]);
        $this->queueRepository->delete($queue);
    }
}