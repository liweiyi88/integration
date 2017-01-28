<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class MailchimpOptInCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('sqs:mailchimp')
            ->addOption('sleep', null, InputOption::VALUE_REQUIRED, null, 20)
            ->setDescription('integrate with Mailchimp');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $client = $this->getContainer()->get('aws.sqs.helper');
        $url = $client->getQueueUrl($this->getContainer()->getParameter('mailchimp_queue'));
        $sleepTime = intval($input->getOption('sleep'));

        while (true) {
            try {
                $result = $client->receiveMessage($url);
                if ($result->get('Messages')) {
                    foreach ($result->get('Messages') as $message) {
                        $receiptHandle = $message['ReceiptHandle'];
                        $messageBody = $message['Body'];
                        $messageJson = json_decode($messageBody, true);
                        $user = json_decode($messageJson['Message'], true);

                        $emailBody = \Swift_Message::newInstance()
                            ->setSubject('Mailchimp Integration')
                            ->setFrom('weiyi.li713@gmail.com')
                            ->setTo($user['email'])
                            ->setBody('Your information have been pushed to Mailchimp!');

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
