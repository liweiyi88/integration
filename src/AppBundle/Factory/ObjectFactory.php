<?php

namespace AppBundle\Factory;

use Symfony\Component\Serializer\Serializer;

class ObjectFactory
{
    private $serializer;

    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    public function get(string $message)
    {
        $array = json_decode($message, true);
        return $this->serializer->deserialize($message, $array['class'], 'json');
    }
}
