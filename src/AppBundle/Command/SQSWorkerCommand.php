<?php
namespace AppBundle\Command;

use AppBundle\Messaging\ConfirmationEmail;
use AppBundle\Messaging\Mailchimp;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class SQSWorkerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('process:queue')
            ->addOption('max_memory', null, InputOption::VALUE_REQUIRED, null, 128)
            ->addOption('sleep', null, InputOption::VALUE_REQUIRED, null, 5)
            ->addOption('handler', null, InputOption::VALUE_REQUIRED, null, null)
            ->addOption('cache', null, InputOption::VALUE_REQUIRED, null, null)
            ->addOption('max_number_messages', null, InputOption::VALUE_REQUIRED, null, 10)
            ->addOption('wait_time_seconds', null, InputOption::VALUE_REQUIRED, null, 20)
            ->setDescription('Process AWS SQS messages');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sqs = $this->getContainer()->get('aws.sqs.helper');
        $maxNumberOfMessages = intval($input->getOption('max_number_messages'));
        $waitTimeSeconds = intval($input->getOption('wait_time_seconds'));
        $sleepSeconds = intval($input->getOption('sleep'));
        $handler = $input->getOption('handler');

        $worker = $this->getContainer()->get('worker');
        $cache = $this->getContainer()->get('cache.factory')->get($input->getOption('cache'));
        $worker->setCache($cache);

        $lastRestart = $cache->getItem('last_restart_date')->get();
        $url = $sqs->getQueueUrl($this->getQueueName($handler));

        while (true) {
            try {
                $result = $sqs->receiveMessage($url, $maxNumberOfMessages, $waitTimeSeconds);
                if ($result->get('Messages')) {
                    $messageHandler = $this->getContainer()->get('message.handler.factory')->get($handler);
                    foreach ($result->get('Messages') as $message) {
                        $messageHandler->handle($message);
                        $sqs->deleteMessage($url, $message['ReceiptHandle']);
                    }
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
            case ConfirmationEmail::ALIAS:
                return $this->getContainer()->getParameter('confirmation_queue');
            case Mailchimp::ALIAS:
                return  $this->getContainer()->getParameter('mailchimp_queue');
        }

        throw new \InvalidArgumentException('Unsupported Queue');
    }
}
