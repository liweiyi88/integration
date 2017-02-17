<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class WorkRestartCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('worker:restart')
            ->setDescription('Hello PhpStorm');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $redisConnection = RedisAdapter::createConnection($this->getContainer()->getParameter('redis_dsn'));
        $cache = new RedisAdapter($redisConnection);

        $lastRestartDateItem = $cache->getItem('last_restart_date');
        $cache->save($lastRestartDateItem->set(date('Y-m-d H:i:s')));
    }
}
