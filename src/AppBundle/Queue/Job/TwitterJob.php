<?php

namespace AppBundle\Queue\Job;

class TwitterJob implements Job
{
    const QUEUE_NAME = 'twitter';
    const MAX_NUMBER_OF_MESSAGES = 1;
    const WAIT_TIME_SECONDS = 20;

    private $payload;

    public function __construct(string $payload = null)
    {
        $this->payload = $payload;
    }

    public function getQueueName(): string
    {
        return self::QUEUE_NAME;
    }

    public function getMaxNumberOfMessages(): int
    {
        return self::MAX_NUMBER_OF_MESSAGES;
    }

    public function getWaitTimeSeconds(): int
    {
        return self::WAIT_TIME_SECONDS;
    }

    public function getPayload(): string
    {
        return $this->payload;
    }

    public function setPayload(string $payload): void
    {
        $this->payload = $payload;
    }
}
