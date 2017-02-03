<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class WorkerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('sqs:process')
            ->addOption('sleep', null, InputOption::VALUE_REQUIRED, null, 20)
            ->addOption('processor', null, InputOption::VALUE_REQUIRED, null, null)
            ->addOption('max_number_messages', null, InputOption::VALUE_REQUIRED, null, 1)
            ->addOption('wait_time_seconds', null, InputOption::VALUE_REQUIRED, null, 0)
            ->setDescription('Process AWS SQS messages');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = $this->getContainer()->get('aws.sqs.helper');
        $url = $client->getQueueUrl($this->getContainer()->getParameter('mailchimp_queue'));
        $maxNumberOfMessages = intval($input->getOption('max_number_messages'));
        $waitTimeSeconds = intval($input->getOption('wait_time_seconds'));
        $sleepTime = intval($input->getOption('sleep'));
        $processor = $input->getOption('processor');

        while (true) {
            try {
                $result = $client->receiveMessage($url, $maxNumberOfMessages, $waitTimeSeconds);
                if ($result->get('Messages')) {
                    $this->createProcessor($processor)->process($result->get('Messages'));
                } else {
                    sleep($sleepTime);
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
    }

    private function createProcessor($name)
    {
        if ($name == 'confirmation_email') {
            return $this->getContainer()->get('confirmation.email.processor');
        } elseif ($name == 'mailchimp') {
            return $this->getContainer()->get('mailchimp.processor');
        }
    }
}
