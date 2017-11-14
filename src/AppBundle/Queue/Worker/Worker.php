<?php

namespace AppBundle\Queue\Worker;

use Symfony\Component\Cache\Adapter\AdapterInterface;

class Worker
{
    private $cache;

    public function __construct(AdapterInterface $cache)
    {
        $this->cache = $cache;
    }

    public function memoryExceeded(int $memoryLimit): bool
    {
        return (memory_get_usage() / 1024 / 1024) >= $memoryLimit;
    }

    public function sleep(int $seconds): void
    {
        sleep($seconds);
    }

    public function stop(int $status = 0): void
    {
        exit($status);
    }

    public function stopIfNecessary(int $memoryLimit, string $lastRestart): void
    {
        if ($this->memoryExceeded($memoryLimit)) {
            $this->stop(12);
        } elseif ($this->queueShouldRestart($lastRestart)) {
            $this->stop();
        }
    }

    public function queueShouldRestart(?string $lastRestart): bool
    {
        return $this->getTimestampOfLastQueueRestart() != $lastRestart;
    }

    /**
     * @throws \Exception
     */
    public function getTimestampOfLastQueueRestart(): ?string
    {
        if ($this->cache) {
            return $this->cache->getItem('last_restart_date')->get();
        }

        throw new \Exception('Cache is required');
    }
}
