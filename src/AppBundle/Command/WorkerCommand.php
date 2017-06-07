<?php

namespace AppBundle\Command;

use AppBundle\BaseCommandBus;
use AppBundle\CommandBus\AddRemoveFromQueueCommandBus;
use AppBundle\CommandBus\CommandBus;
use AppBundle\Factory\CacheFactory;
use AppBundle\Factory\CommandFactory;
use AppBundle\Queue\SQS;
use AppBundle\Worker;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class WorkerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('process:queue')
            ->addOption('max_memory', null, InputOption::VALUE_REQUIRED, null, 128)
            ->addOption('sleep', null, InputOption::VALUE_REQUIRED, null, 5)
            ->addOption('queue_name', null, InputOption::VALUE_REQUIRED, 'Command', null)
            ->addOption('cache', null, InputOption::VALUE_REQUIRED, null, null)
            ->addOption('max_number_messages', null, InputOption::VALUE_REQUIRED, null, 10)
            ->addOption('wait_time_seconds', null, InputOption::VALUE_REQUIRED, null, 20)
            ->setDescription('Process AWS SQS messages');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sqs = $this->getContainer()->get(SQS::class);
        $worker = $this->getContainer()->get(Worker::class);
        $commandFactory = $this->getContainer()->get(CommandFactory::class);
        $cacheFactory = $this->getContainer()->get(CacheFactory::class);
        $innerCommandBus = $this->getContainer()->get(CommandBus::class);
        $entityManager = $this->getContainer()->get('doctrine')->getManager();
        $commandBus = new AddRemoveFromQueueCommandBus($innerCommandBus, $entityManager);
        $logger = $this->getContainer()->get('logger');

        $maxNumberOfMessages = intval($input->getOption('max_number_messages'));
        $waitTimeSeconds = intval($input->getOption('wait_time_seconds'));
        $sleepSeconds = intval($input->getOption('sleep'));
        $queueName = $input->getOption('queue_name');

        $cache = $cacheFactory->get($input->getOption('cache'));
        $worker->setCache($cache);

        //TODO: will this throw any exception?
        $url = $sqs->getQueueUrl($queueName);

        while (true) {
            try {
                $messages = $sqs->getMessages($url, $maxNumberOfMessages, $waitTimeSeconds);
                if (count($messages) > 0) {
                    foreach ($messages as $message) {
                        $command = $commandFactory->get($sqs->getRawBody($message));
                        $commandBus->handle($command);
                        $sqs->deleteMessage($url, $message);
                    }
                } else {
                    $worker->sleep($sleepSeconds);
                }

                $worker->stopIfNecessary(intval($input->getOption('max_memory')));
            } catch (\Exception $e) {
                dump($e);
                $logger->error($e->getMessage());
            }
        }
    }
}