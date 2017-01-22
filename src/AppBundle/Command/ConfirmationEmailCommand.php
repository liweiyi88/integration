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
            ->setName('confirm:email')
            ->setDescription('Process confirmation email');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
//        $confirmationQueueUrl = $this->getContainer()->getParameter('');
//        $message = $this->getContainer()->get('aws.sqs.helper')->receiveMessage()
//
//        while (true) {
//            $message = $queue->receive();
//            if ($message) {
//                try {
//                    $message->process();
//                    $queue->delete($message);
//                } catch (Exception $e) {
//                    $queue->release($message);
//                    echo $e->getMessage();
//                }
//            } else {
//                // Wait 20 seconds if no jobs in queue to minimise requests to AWS API
//                sleep(20);
//            }
//        }

    }
}
