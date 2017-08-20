<?php

namespace AppBundle\Queue\Job;

interface Job
{
    public function getQueueName(): string;
    public function getMaxNumberOfMessages(): int;
    public function getWaitTimeSeconds(): int;
    public function getPayload(): string;
    public function setPayload(string $payload);
}
