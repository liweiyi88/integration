<?php

namespace AppBundle\Queue\Worker;

use Symfony\Component\Cache\Adapter\AdapterInterface;

class WorkerFactory
{
    public static function create(AdapterInterface $cache)
    {
        return new Worker($cache);
    }
}