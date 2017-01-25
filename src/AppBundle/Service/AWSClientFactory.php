<?php
namespace AppBundle\Service;

use Aws\Sns\SnsClient;
use Aws\Sqs\SqsClient;

class AWSClientFactory
{
    private $version;
    private $region;
    private $key;
    private $secret;
    private $config;

    public function __construct($version, $region, $key, $secret)
    {
        $this->version = $version;
        $this->region = $region;
        $this->key = $key;
        $this->secret = $secret;
        $this->config = [
            'version' => $this->version,
            'region' => $this->region,
            'credentials' => [
                'key' => $this->key,
                'secret' => $this->secret
            ]
        ];
    }

    public function create($clientType)
    {
        if ($clientType == 'sns') {
            return new SnsClient($this->config);
        } elseif ($clientType == 'sqs') {
            return new SqsClient($this->config);
        }

        throw new \Exception('No such aws client');
    }
}