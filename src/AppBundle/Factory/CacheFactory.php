<?php

namespace AppBundle\Factory;

use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CacheFactory
{
    const FILE_SYSTEM = 'file_system';

    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function get(string $cache): AbstractAdapter
    {
        switch ($cache) {
            case self::FILE_SYSTEM:
                return new FilesystemAdapter();
        }

        throw new \InvalidArgumentException('Unsupported cache');
    }
}
