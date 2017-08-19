<?php

namespace AppBundle\Queue;

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
        return $this->client->sendMessage([
            'QueueUrl' => $this->getQueueUrl(),
            'MessageBody' => $this->job->getPayload()
        ])->get('MessageId');
    }

    public function getQueueUrl(): ?string
    {
        $result = $this->client->getQueueUrl([
            'QueueName' => $this->job->getName()
        ]);

        return $result->get('QueueUrl');
    }

    public function receiveMessage(): ?Result
    {
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

    public function setJob(Job $job): void
    {
        $this->job = $job;
    }
}
