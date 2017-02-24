<?php
namespace AppBundle\Service\Aws;

use Aws\Sqs\SqsClient;

class SQS
{
    private $client;

    public function __construct(SqsClient $client)
    {
        $this->client = $client;
    }

    public function getQueueUrl($queueName)
    {
        $result = $this->client->getQueueUrl([
            'QueueName' => $queueName
        ]);

        return $result->get('QueueUrl');
    }

    public function receiveMessage($queueUrl, $maxNumberOfMessages = 1, $waitTimeSeconds = 0)
    {
        return $this->client->receiveMessage([
            'QueueUrl' => $queueUrl,
            'MaxNumberOfMessages' => $maxNumberOfMessages,
            'WaitTimeSeconds' => $waitTimeSeconds
        ]);
    }

    public function deleteMessage($url, $receiptHandle)
    {
        $this->client->deleteMessage([
            'QueueUrl' => $url,
            'ReceiptHandle' => $receiptHandle
        ]);
    }
}
