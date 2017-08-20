<?php

namespace AppBundle\Queue;

use AppBundle\Queue\Job\Job;
use Aws\Sqs\SqsClient;
use Aws\Result;

class SQS implements Queueable
{
    private $client;
    private $job;

    public function __construct(SqsClient $client)
    {
        $this->client = $client;
    }

    public function push(): ?string
    {
        $this->stopIfInvalidJob();

        return $this->client->sendMessage([
            'QueueUrl' => $this->getQueueUrl(),
            'MessageBody' => $this->job->getPayload()
        ])->get('MessageId');
    }

    public function getQueueUrl(): ?string
    {
        $this->stopIfInvalidJob();

        if ($this->job->getQueueName() === null) {
            throw new \Exception('queue name is required');
        }

        $result = $this->client->getQueueUrl([
            'QueueName' => $this->job->getQueueName()
        ]);

        return $result->get('QueueUrl');
    }

    public function receiveMessage(): ?Result
    {
        $this->stopIfInvalidJob();

        return $this->client->receiveMessage([
            'QueueUrl' => $this->getQueueUrl(),
            'MaxNumberOfMessages' => $this->job->getMaxNumberOfMessages(),
            'WaitTimeSeconds' => $this->job->getWaitTimeSeconds()
        ]);
    }

    public function getMessages(): ?array
    {
        $result = $this->receiveMessage();

        return $result->get('Messages');
    }

    public function getRawBody(array $message): ?string
    {
        return $message['Body'];
    }

    public function deleteMessage(array $message): void
    {
        $this->client->deleteMessage([
            'QueueUrl' => $this->getQueueUrl(),
            'ReceiptHandle' => $message['ReceiptHandle']
        ]);
    }

    public function setJob(Job $job): Queueable
    {
        $this->job = $job;
        return $this;
    }

    private function stopIfInvalidJob(): void
    {
        if ($this->job === null) {
            throw new \Exception('a job is required');
        }

        if ($this->job->getQueueName() === null) {
            throw new \Exception('the queue name is required');
        }

        if ($this->job->getMaxNumberOfMessages() === null) {
            throw new \Exception('the max number of message is required');
        }

        if ($this->job->getWaitTimeSeconds() === null) {
            throw new \Exception('the wait time seconds is required');
        }
    }
}
