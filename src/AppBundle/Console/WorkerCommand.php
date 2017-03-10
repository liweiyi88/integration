<?php
namespace AppBundle\Console;

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
            ->addOption('processor', null, InputOption::VALUE_REQUIRED, null, null)
            ->addOption('cache', null, InputOption::VALUE_REQUIRED, null, null)
            ->addOption('max_number_messages', null, InputOption::VALUE_REQUIRED, null, 10)
            ->addOption('wait_time_seconds', null, InputOption::VALUE_REQUIRED, null, 20)
            ->setDescription('Process AWS SQS messages');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = $this->getContainer()->get('aws.sqs.helper');
        $maxNumberOfMessages = intval($input->getOption('max_number_messages'));
        $waitTimeSeconds = intval($input->getOption('wait_time_seconds'));
        $sleepSeconds = intval($input->getOption('sleep'));
        $processorName = $input->getOption('processor');

        $worker = $this->getContainer()->get('worker');
        $cache = $this->getContainer()->get('cache.factory')->get($input->getOption('cache'));
        $worker->setCache($cache);

        $lastRestart = $cache->getItem('last_restart_date')->get();
        $url = $client->getQueueUrl($this->getQueueName($processorName));

        while (true) {
            try {
                $result = $client->receiveMessage($url, $maxNumberOfMessages, $waitTimeSeconds);
                if ($result->get('Messages')) {
                    $processor = $this->getContainer()->get('processor.factory')->get($processorName);
                    $this->getContainer()->get('message.handler')->handle($processor, $url, $result->get('Messages'));
                } else {
                    $worker->sleep($sleepSeconds);
                }

                $worker->stopIfNecessary(intval($input->getOption('max_memory')), $lastRestart);
            } catch (\Exception $e) {
                $this->getContainer()->get('logger')->error($e->getMessage());
            }
        }
    }

    /**
     * @param string $name
     * @return mixed
     */
    private function getQueueName($name)
    {
        switch ($name) {
            case 'confirmation_email':
                return $this->getContainer()->getParameter('confirmation_queue');
            case 'mailchimp':
                return  $this->getContainer()->getParameter('mailchimp_queue');
        }

        throw new \InvalidArgumentException('Unsupported Queue');
    }
}
