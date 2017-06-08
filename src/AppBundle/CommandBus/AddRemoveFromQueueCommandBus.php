<?php

namespace AppBundle\CommandBus;

use AppBundle\Entity\Content;
use AppBundle\Model\Command;
use AppBundle\Model\ConfirmationEmail;
use AppBundle\Model\Mailchimp;
use Doctrine\ORM\EntityManager;
use AppBundle\Enum\Queue;

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

        if ($command instanceof ConfirmationEmail) {
            $queue = Queue::CONFIRMATION;
        } elseif ($command instanceof Mailchimp) {
            $queue = Queue::MAILCHIMP;
        } else {
            throw new \RuntimeException('invalid command object');
        }

        $queue = $this->entityManager->getRepository(Content::class)->findOneBy(
            [
                'email' => $command->getEmail(),
                'queue' => $queue
            ]
        );

        if ($queue) {
            $this->entityManager->remove($queue);
            $this->entityManager->flush();
        }
    }
}
