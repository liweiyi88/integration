<?php
namespace AppBundle\Service;


use Aws\Sns\Exception\SnsException;
use Aws\Sns\SnsClient;

class SNSHelper
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
            dump($e);
            //put exception into log or send email.
        }
    }

}