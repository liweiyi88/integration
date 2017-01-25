<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ConfirmationEmailCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('sqs:confirm:email')
            ->setDescription('Process confirmation email');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = $this->getContainer()->get('aws.sqs.helper');
        $url = $client->getQueueUrl($this->getContainer()->getParameter('confirmation_queue'));

        while (true) {
            $result = $client->receiveMessage($url);
            if (isset($result['Messages'])) {
                try {
                    $resultMessage = array_pop($result['Messages']);
                    $receiptHandle = $resultMessage['ReceiptHandle'];
                    $messageBody = $resultMessage['Body'];

                    $messageJson = json_decode($messageBody, true);
                    $message = json_decode($messageJson['Message'], true);

                    //TODO
                    //send email

                    $client->deleteMessage($url, $receiptHandle);
                } catch (\Exception $e) {
                    echo $e->getMessage();
                }
            } else {
                // Wait 20 seconds if no jobs in queue to minimise requests to AWS API
                sleep(20);
            }
        }
    }
}
