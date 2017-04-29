<?php
namespace AppBundle\Service;

use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CacheFactory
{
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param $cache
     * @return AbstractAdapter
     */
    public function get($cache)
    {
        switch ($cache) {
            case 'redis':
                return new RedisAdapter(RedisAdapter::createConnection($this->container->getParameter('redis_dsn')));
            case 'file_system':
                return new FilesystemAdapter();
        }

        throw new \InvalidArgumentException('Unsupported cache');
    }
}
