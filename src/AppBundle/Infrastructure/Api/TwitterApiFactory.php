<?php

namespace AppBundle\Infrastructure\Api;

use Abraham\TwitterOAuth\TwitterOAuth;

class TwitterApiFactory
{
    private $consumerKey;
    private $consumerSecret;
    private $accessToken;
    private $accessTokenSecret;

    public function __construct(
        $consumerKey,
        $consumerSecret,
        $accessToken,
        $accessTokenSecret
    ) {
        $this->consumerKey = $consumerKey;
        $this->consumerSecret = $consumerSecret;
        $this->accessToken = $accessToken;
        $this->accessTokenSecret = $accessTokenSecret;
    }

    public function create()
    {
        return new TwitterOAuth($this->consumerKey, $this->consumerSecret, $this->accessToken, $this->accessTokenSecret);
    }
}
