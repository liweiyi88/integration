<?php
namespace AppBundle\Service;


use Symfony\Component\Cache\Adapter\AdapterInterface;

class Worker
{
    private $cache;

    /**
     * @param int $memoryLimit
     * @return bool
     */
    public function memoryExceeded($memoryLimit)
    {
        return (memory_get_usage() / 1024 / 1024) >= $memoryLimit;
    }

    /**
     * @param int $seconds
     */
    public function sleep($seconds)
    {
        sleep($seconds);
    }

    /**
     * @param int $status
     */
    public function stop($status = 0)
    {
        exit($status);
    }

    /**
     * @param int $memoryLimit
     * @param string $lastRestart
     */
    public function stopIfNecessary($memoryLimit, $lastRestart)
    {
        if ($this->memoryExceeded($memoryLimit)) {
            $this->stop(12);
        } elseif ($this->queueShouldRestart($lastRestart)) {
            $this->stop();
        }
    }

    /**
     * @param string $lastRestart
     * @return bool
     */
    public function queueShouldRestart($lastRestart)
    {
        return $this->getTimestampOfLastQueueRestart($this->cache) != $lastRestart;
    }

    public function getTimestampOfLastQueueRestart()
    {
        if ($this->cache) {
            return $this->cache->getItem('last_restart_date')->get();
        }
    }

    /**
     * @param AdapterInterface $cache
     */
    public function setCache(AdapterInterface $cache)
    {
        $this->cache = $cache;
    }

}