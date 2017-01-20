<?php
namespace AppBundle\Service;

use Aws\Sns\SnsClient;

class AWSClientFactory
{
    private $version;
    private $region;
    private $key;
    private $secret;

    public function __construct($version, $region, $key, $secret)
    {
        $this->version = $version;
        $this->region = $region;
        $this->key = $key;
        $this->secret = $secret;
    }

    public function createClient($clientType)
    {
        if ($clientType == 'sns') {
            return new SnsClient(
                [
                    'version' => $this->version,
                    'region' => $this->region,
                    'credentials' => [
                        'key' => $this->key,
                        'secret' => $this->secret
                    ]
                ]
            );
        }

        throw new \Exception('No such aws client');
    }
}