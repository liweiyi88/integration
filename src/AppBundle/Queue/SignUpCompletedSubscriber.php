<?php

namespace AppBundle\Queue;

use AppBundle\Queue\Infrastructure\Queueable;
use AppBundle\SignUp\SignUpEvent;
use AppBundle\SignUp\SignUpEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Serializer\Serializer;

class SignUpCompletedSubscriber implements EventSubscriberInterface
{
    private $serializer;
    private $queue;

    public function __construct(Serializer $serializer, Queueable $queue)
    {
        $this->serializer = $serializer;
        $this->queue = $queue;
    }

    public static function getSubscribedEvents()
    {
        return [
            SignUpEvents::SIGNUP_COMPLETED => 'onSignUpCompleted'
        ];
    }

    public function onSignUpCompleted(SignUpEvent $event)
    {
        $signUp = $event->getSignUp();
        $payload = $this->serializer->serialize($signUp, 'json');

        $job = new TwitterJob($payload);
        $this->queue->setJob($job);
        $this->queue->push();
    }
}
