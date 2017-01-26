<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MailchimpOptInCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('sqs:mailchimp')
            ->setDescription('integrate with Mailchimp');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = $this->getContainer()->get('aws.sqs.helper');
        $url = $client->getQueueUrl($this->getContainer()->getParameter('mailchimp_queue'));

        while (true) {
            try {
                $result = $client->receiveMessage($url);
                if (isset($result['Messages'])) {
                    $resultMessage = array_pop($result['Messages']);
                    $receiptHandle = $resultMessage['ReceiptHandle'];
                    $messageBody = $resultMessage['Body'];

                    $messageJson = json_decode($messageBody, true);
                    $message = json_decode($messageJson['Message'], true);
                    $emailBody = \Swift_Message::newInstance()
                        ->setSubject('Mailchimp Integration')
                        ->setFrom('weiyi.li713@gmail.com')
                        ->setTo($message['email'])
                        ->setBody('Your information have been pushed to Mailchimp!');
                    ;
                    $this->getContainer()->get('mailer')->send($emailBody);

                    $client->deleteMessage($url, $receiptHandle);
                } else {
                    // Wait 20 seconds if no jobs in queue to minimise requests to AWS API
                    sleep(20);
                }
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
    }
}
