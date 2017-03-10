<?php
namespace AppBundle\Service;

use Symfony\Component\Cache\Adapter\AbstractAdapter;

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
        }

        throw new \InvalidArgumentException('no such a cache connection');
    }
}