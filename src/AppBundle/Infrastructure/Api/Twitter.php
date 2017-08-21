<?php

namespace AppBundle\Infrastructure\Api;

use GuzzleHttp\Client;

class Twitter
{
    const END_POINT = 'https://api.twitter.com/1.1';

    private $accessToken;
    private $accessTokenSecret;
    private $httpClient;

    public function __construct(
        $accessToken,
        $accessTokenSecret,
        Client $httpClient
    ) {
        $this->accessToken = $accessToken;
        $this->accessTokenSecret = $accessTokenSecret;
        $this->httpClient = $httpClient;
    }

    public function push(string $message)
    {
        $url = self::END_POINT.'/statuses/update.json';
        http_build_query(['status' => $message]);

        $response = $this->httpClient->post($url);

        dump($response);
    }
}