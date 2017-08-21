<?php

namespace AppBundle\Infrastructure\Api;

use GuzzleHttp\Client;

class HttpClientFactory
{
    private $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    public function get()
    {
        return $this->client;
    }
}