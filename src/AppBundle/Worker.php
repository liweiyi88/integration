<?php

namespace AppBundle;

use Symfony\Component\Cache\Adapter\AdapterInterface;

class Worker
{
    /**@var AdapterInterface $cache **/
    private $cache;

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

    public function stopIfNecessary(int $memoryLimit): void
    {
        if ($this->memoryExceeded($memoryLimit)) {
            $this->stop(12);
        } elseif ($this->queueShouldRestart($this->getTimestampOfLastQueueRestart())) {
            $this->stop();
        }
    }

    public function queueShouldRestart(string $lastRestart): bool
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

    public function setCache(AdapterInterface $cache): void
    {
        $this->cache = $cache;
    }
}