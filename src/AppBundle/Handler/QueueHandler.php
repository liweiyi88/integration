<?php

namespace AppBundle\Handler;


use AppBundle\Entity\SignUp;
use AppBundle\Entity\User;
use AppBundle\Repository\QueueRepository;

class QueueHandler implements UserHandler
{
    private $queueRepository;

    public function __construct(QueueRepository $queueRepository)
    {
        $this->queueRepository = $queueRepository;
    }

    public function handle(SignUp $signUp)
    {
        $queue = $this->queueRepository->findBy(['email' => $signUp->getEmail()]);
        $this->queueRepository->delete($queue);
    }
}