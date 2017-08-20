<?php

namespace AppBundle\Command;

use AppBundle\Factory\CacheFactory;
use AppBundle\Factory\ObjectFactory;
use AppBundle\Infrastructure\Api\Twitter;
use AppBundle\Queue\Job\TwitterJob;
use AppBundle\Queue\SQS;
use AppBundle\Worker;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class SignUpTwitterWorkerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('process:queue')
            ->addOption('max_memory', null, InputOption::VALUE_REQUIRED, null, 128)
            ->addOption('sleep', null, InputOption::VALUE_REQUIRED, null, 5)
            ->addOption('cache', null, InputOption::VALUE_REQUIRED, null, null)
            ->setDescription('Process AWS SQS messages');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sqs = $this->getContainer()->get(SQS::class);
        $worker = $this->getContainer()->get(Worker::class);
        $objectFactory = $this->getContainer()->get(ObjectFactory::class);
        $cacheFactory = $this->getContainer()->get(CacheFactory::class);
        $twitterApi = $this->getContainer()->get(Twitter::class);
        $logger = $this->getContainer()->get('logger');

        $sleepSeconds = intval($input->getOption('sleep'));

        $cache = $cacheFactory->get($input->getOption('cache'));
        $worker->setCache($cache);

        //TODO: will this throw any exception?
        $job = new TwitterJob();
        $sqs->setJob($job);

        while (true) {
            sleep(2); //this line is unnecessary. It is only for demo use.
            try {
                $messages = $sqs->getMessages();
                if (count($messages) > 0) {
                    foreach ($messages as $message) {
                        $command = $objectFactory->get($sqs->getRawBody($message));
                        $twitterApi->push($command);
                        $sqs->deleteMessage($message);
                    }
                } else {
                    $worker->sleep($sleepSeconds);
                }

                $worker->stopIfNecessary(intval($input->getOption('max_memory')));
            } catch (\Exception $e) {
                $logger->error($e->getMessage());
            }
        }
    }
}
