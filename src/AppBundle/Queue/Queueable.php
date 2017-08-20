<?php

namespace AppBundle\Queue;

use AppBundle\Queue\Job\Job;

interface Queueable
{
    public function push();
    public function receiveMessage();
    public function deleteMessage(array $message);
    public function getRawBody(array $message);
    public function getMessages(): ?array ;
    public function setJob(Job $job): Queueable;
}