<?php

namespace AppBundle\Factory;

use Symfony\Component\Cache\Adapter\AbstractAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

class CacheFactory
{
    const FILE_SYSTEM = 'file_system';

    public static function create(string $cache): AbstractAdapter
    {
        switch ($cache) {
            case self::FILE_SYSTEM:
                return new FilesystemAdapter();
        }

        throw new \InvalidArgumentException('Unsupported cache');
    }
}
