<?php
namespace AppBundle\Service\Aws;

use Aws\Sns\Exception\SnsException;
use Aws\Sns\SnsClient;

class SNS
{
    private $client;

    public function __construct(SnsClient $client)
    {
        $this->client = $client;
    }

    public function publish($message, $subject, $arn)
    {
        try {
            $this->client->publish([
                'Message' => $message,
                'Subject' => $subject,
                'TopicArn' => $arn
            ]);
        } catch (SnsException $e) {
            //put exception into log or send email.
        }
    }
}
