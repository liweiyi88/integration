<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ConfirmationEmailCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('sqs:confirm:email')
            ->addOption('sleep', null, InputOption::VALUE_REQUIRED, null, 20)
            ->addOption('max_number_messages', null, InputOption::VALUE_REQUIRED, null, 1)
            ->addOption('wait_time_seconds', null, InputOption::VALUE_REQUIRED, null, 0)
            ->setDescription('Process confirmation email');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = $this->getContainer()->get('aws.sqs.helper');
        $url = $client->getQueueUrl($this->getContainer()->getParameter('confirmation_queue'));
        $maxNumberOfMessages = intval($input->getOption('max_number_messages'));
        $waitTimeSeconds = intval($input->getOption('wait_time_seconds'));
        $sleepTime = intval($input->getOption('sleep'));

        while (true) {
            try {
                $result = $client->receiveMessage($url, $maxNumberOfMessages, $waitTimeSeconds);

                if ($result->get('Messages')) {
                    foreach ($result->get('Messages') as $message) {
                        $receiptHandle = $message['ReceiptHandle'];
                        $messageBody = $message['Body'];
                        $messageJson = json_decode($messageBody, true);
                        $user = json_decode($messageJson['Message'], true);

                        $emailBody = \Swift_Message::newInstance()
                            ->setSubject('Email Confirmation')
                            ->setFrom('weiyi.li713@gmail.com')
                            ->setTo($user['email'])
                            ->setBody('Your application has been confirmed! Thanks for the registration');

                        ;
                        $this->getContainer()->get('mailer')->send($emailBody);

                        $client->deleteMessage($url, $receiptHandle);
                    }
                } else {
                    sleep($sleepTime);
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
    }
}
