<?php

namespace AppBundle\Queue;

interface Job
{
    public function getName(): string;
    public function getMaxNumberOfMessages(): int;
    public function getWaitTimeSeconds(): int;
    public function getPayload(): string;
    public function setPayload(string $payload);
}
