<?php

namespace AppBundle\Queue;

use Aws\Sqs\SqsClient;
use Aws\Result;

class SQS
{
    private $client;

    public function __construct(SqsClient $client)
    {
        $this->client = $client;
    }

    public function push(string $payload, string $queue): ?string
    {
        return $this->client->sendMessage([
            'QueueUrl' => $this->getQueueUrl($queue),
            'MessageBody' => $payload
        ])->get('MessageId');
    }

    public function getQueueUrl($queueName): ?string
    {
        $result = $this->client->getQueueUrl([
            'QueueName' => $queueName
        ]);

        return $result->get('QueueUrl');
    }

    public function receiveMessage(string $queueUrl, int $maxNumberOfMessages = 1, int $waitTimeSeconds = 0): ?Result
    {
        return $this->client->receiveMessage([
            'QueueUrl' => $queueUrl,
            'MaxNumberOfMessages' => $maxNumberOfMessages,
            'WaitTimeSeconds' => $waitTimeSeconds
        ]);
    }

    public function getMessages(string $queueUrl, int $maxNumberOfMessages = 1, int $waitTimeSeconds = 0): ?array
    {
        $result = $this->receiveMessage($queueUrl, $maxNumberOfMessages, $waitTimeSeconds);

        return $result->get('Messages');
    }

    public function getRawBody(array $message): ?string
    {
        return $message['Body'];
    }

    public function deleteMessage(string $url, array $message): void
    {
        $this->client->deleteMessage([
            'QueueUrl' => $url,
            'ReceiptHandle' => $message['ReceiptHandle']
        ]);
    }
}