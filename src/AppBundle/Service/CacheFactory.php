<?php
namespace AppBundle\Service;

use Symfony\Component\Cache\Adapter\AbstractAdapter;

class CacheFactory
{
    /**
     * @param $name
     * @return AbstractAdapter
     */
    public function getCache($name)
    {
        switch ($name) {
            case 'redis':
                return new RedisAdapter(RedisAdapter::createConnection($this->getContainer()->getParameter('redis_dsn')));
        }

        throw new \InvalidArgumentException('no such a cache connection');
    }
}