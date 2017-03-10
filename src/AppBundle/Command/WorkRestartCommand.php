<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class WorkRestartCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('worker:restart')
            ->addOption('cache', null, InputOption::VALUE_REQUIRED, null, null)
            ->setDescription('Safely stop running worker');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cache = $this->getContainer()->get('cache.factory')->getCache($input->getOption('cache'));
        $lastRestartDateItem = $cache->getItem('last_restart_date');
        $cache->save($lastRestartDateItem->set(date('Y-m-d H:i:s')));
    }
}
