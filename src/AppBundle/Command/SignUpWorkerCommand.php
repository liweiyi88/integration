<?php
namespace AppBundle\Command;

use AppBundle\Model\Command;
use AppBundle\Model\ConfirmationEmail;
use AppBundle\Model\Mailchimp;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class SignUpWorkerCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('process:queue')
            ->addOption('max_memory', null, InputOption::VALUE_REQUIRED, null, 128)
            ->addOption('sleep', null, InputOption::VALUE_REQUIRED, null, 5)
            ->addOption('command_name', null, InputOption::VALUE_REQUIRED, 'Command', null)
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
        $commandName = $input->getOption('command_name');

        $worker = $this->getContainer()->get('worker');
        $cache = $this->getContainer()->get('cache.factory')->get($input->getOption('cache'));
        $worker->setCache($cache);

        $lastRestart = $cache->getItem('last_restart_date')->get();
        $url = $sqs->getQueueUrl($this->getQueueName($commandName));

        while (true) {
            try {
                $result = $sqs->receiveMessage($url, $maxNumberOfMessages, $waitTimeSeconds);
                if ($result->get('Messages')) {
                    $commandBus = $this->getContainer()->get('command.bus');
                    foreach ($result->get('Messages') as $message) {
                        $command = $this->getCommand($message, $commandName);
                        if ($command instanceof Command) {
                            $commandBus->handle($command);
                            $sqs->deleteMessage($url, $message['ReceiptHandle']);
                        }
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
     * @param string $message
     * @param string $commandName
     * @return mixed|null
     */
    private function getCommand($message, $commandName)
    {
        if ($message != null) {
            $deserializer = $this->getContainer()->get('jms_serializer');
            $messageJson = json_decode($message['Body'], true);
            $message = json_decode($messageJson['Message'], true);

            switch ($commandName) {
                case ConfirmationEmail::ALIAS:
                    return $deserializer->deserialize($message, ConfirmationEmail::class, 'json');
                case Mailchimp::ALIAS:
                    return $deserializer->deserialize($message, Mailchimp::class, 'json');
            }
        }

        return null;
    }

    /**
     * @param string $commandName
     * @return string
     * @throws \InvalidArgumentException
     */
    private function getQueueName($commandName)
    {
        switch ($commandName) {
            case ConfirmationEmail::ALIAS:
                return $this->getContainer()->getParameter('confirmation_queue');
            case Mailchimp::ALIAS:
                return  $this->getContainer()->getParameter('mailchimp_queue');
        }

        throw new \InvalidArgumentException('Unsupported Queue');
    }
}
