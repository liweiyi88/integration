<?php
namespace AppBundle\Service;


use Aws\Sqs\SqsClient;

class SQSHelper
{
    private $client;

    public function __construct(SqsClient $client)
    {
        $this->client = $client;
    }

    public function receiveMessage($queueUrl)
    {
        return $this->client->receiveMessage([
            'QueueUrl' => $queueUrl
        ]);
    }
}