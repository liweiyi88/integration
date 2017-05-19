<?php

namespace AppBundle\Handler;


use AppBundle\Entity\User;
use AppBundle\Repository\QueueRepository;

class QueueHandler implements UserHandler
{
    private $queueRepository;

    public function __construct(QueueRepository $queueRepository)
    {
        $this->queueRepository = $queueRepository;
    }

    public function handle(User $user)
    {
        $queue = $this->queueRepository->findBy(['email' => $user->getEmail()]);
        $this->queueRepository->delete($queue);
    }
}