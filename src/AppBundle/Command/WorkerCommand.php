<?php
namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class WorkerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('sqs:process')
            ->addOption('sleep', null, InputOption::VALUE_REQUIRED, null, 5)
            ->addOption('processor', null, InputOption::VALUE_REQUIRED, null, null)
            ->addOption('max_number_messages', null, InputOption::VALUE_REQUIRED, null, 10)
            ->addOption('wait_time_seconds', null, InputOption::VALUE_REQUIRED, null, 20)
            ->setDescription('Process AWS SQS messages');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = $this->getContainer()->get('aws.sqs.helper');
        $maxNumberOfMessages = intval($input->getOption('max_number_messages'));
        $waitTimeSeconds = intval($input->getOption('wait_time_seconds'));
        $sleepTime = intval($input->getOption('sleep'));
        $processorName = $input->getOption('processor');
        $url = $client->getQueueUrl($this->getQueueName($processorName));

        $redisConnection = RedisAdapter::createConnection($this->getContainer()->getParameter('redis_dsn'));
        $cache = new RedisAdapter($redisConnection);

        $lastRestart = $cache->getItem('last_restart_date')->get();

        while (true) {
            try {
                $result = $client->receiveMessage($url, $maxNumberOfMessages, $waitTimeSeconds);
                if ($result->get('Messages')) {
                    $processor = $this->createProcessor($processorName);
                    $this->getContainer()->get('messenger')->send($processor, $url, $result->get('Messages'));
                } else {
                    sleep($sleepTime);
                }

                $this->stopIfNecessary($lastRestart, $cache);
            } catch (\Exception $e) {
                $this->getContainer()->get('logger')->error($e->getMessage());
            }
        }
    }

    private function createProcessor($name)
    {
        if ($name == 'confirmation_email') {
            return $this->getContainer()->get('processor.confirmation.email');
        } elseif ($name == 'mailchimp') {
            return $this->getContainer()->get('processor.mailchimp');
        }

        throw new \InvalidArgumentException('no such a processor');
    }

    private function getQueueName($name)
    {
        if ($name == 'confirmation_email') {
            return $this->getContainer()->getParameter('confirmation_queue');
        } elseif ($name == 'mailchimp') {
            return $this->getContainer()->getParameter('mailchimp_queue');
        }
        throw new \InvalidArgumentException('no such a processor');
    }

    protected function stopIfNecessary($lastRestart, $cache)
    {
        if ($this->queueShouldRestart($lastRestart, $cache)) {
            $this->stop();
        }
    }

    private function stop($status = 0)
    {
        exit($status);
    }

    protected function queueShouldRestart($lastRestart, $cache)
    {
        return $this->getTimestampOfLastQueueRestart($cache) != $lastRestart;
    }

    protected function getTimestampOfLastQueueRestart(RedisAdapter $cache)
    {
        if ($cache) {
            return $cache->getItem('last_restart_date')->get();
        }

        return null;
    }
}
