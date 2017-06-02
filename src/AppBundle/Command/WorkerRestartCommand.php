<?php

namespace AppBundle\Command;

use AppBundle\Factory\CacheFactory;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class WorkerRestartCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('restart:worker')
            ->addOption('cache', null, InputOption::VALUE_REQUIRED, null, null)
            ->setDescription('Safely stop running workers');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cacheFactory = $this->getContainer()->get(CacheFactory::class);
        $cache = $cacheFactory->get($input->getOption('cache'));
        $lastRestartDateItem = $cache->getItem('last_restart_date');
        $cache->save($lastRestartDateItem->set(date('Y-m-d H:i:s')));
        $output->writeln('reset last restart date');
    }
}