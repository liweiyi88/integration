<?php
namespace AppBundle\Service;

use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class CacheFactory
{
    /**
     * @param $cache
     * @return AbstractAdapter
     */
    public function get($cache)
    {
        switch ($cache) {
            case 'redis':
                return new RedisAdapter(RedisAdapter::createConnection($this->getContainer()->getParameter('redis_dsn')));
            case 'file_system':
                return new FilesystemAdapter();
        }

        throw new \InvalidArgumentException('no such a cache connection');
    }
}