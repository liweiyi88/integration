<?php
namespace AppBundle\Service;

use AppBundle\Messaging\MessageProcessInterface;
use AppBundle\Service\Aws\SQS;

class MessageHandler
{
    private $client;

    public function __construct(SQS $client)
    {
        $this->client = $client;
    }

    /**
     * @param MessageProcessInterface $processor
     * @param string                  $queueUrl
     * @param array                   $messages
     */
    public function handle(MessageProcessInterface $processor, $queueUrl, $messages = array())
    {
        foreach ($messages as $message) {
            $processor->process($message);
            $receiptHandle = $message['ReceiptHandle'];
            $this->client->deleteMessage($queueUrl, $receiptHandle);
        }
    }
}
