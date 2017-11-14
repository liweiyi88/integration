<?php

namespace AppBundle\Command;

use Abraham\TwitterOAuth\TwitterOAuth;
use AppBundle\Entity\SignUp;
use AppBundle\Factory\CacheFactory;
use AppBundle\Factory\ObjectFactory;
use AppBundle\Infrastructure\Api\TwitterApiFactory;
use AppBundle\Queue\Job\TwitterJob;
use AppBundle\Queue\SQS;
use AppBundle\Queue\Worker\Worker;
use AppBundle\Queue\Worker\WorkerFactory;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;

class SignUpTwitterWorkerCommand extends ContainerAwareCommand
{
    /** @var SQS $sqs */
    private $sqs;
    /** @var  Worker $worker */
    private $worker;
    /** @var  ObjectFactory $objectFactory **/
    private $objectFactory;
    /** @var  TwitterOAuth */
    private $twitterApi;
    /** @var  LoggerInterface $logger */
    private $logger;
    private $sleepSeconds;

    protected function configure()
    {
        $this
            ->setName('process:queue')
            ->addOption('max_memory', null, InputOption::VALUE_REQUIRED, null, 128)
            ->addOption('sleep', null, InputOption::VALUE_REQUIRED, null, 5)
            ->addOption('cache', null, InputOption::VALUE_REQUIRED, null, null)
            ->setDescription('Process AWS SQS messages');
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->sqs = $this->getContainer()->get(SQS::class);
        $this->sqs->setJob(new TwitterJob());
        $this->objectFactory = $this->getContainer()->get(ObjectFactory::class);
        $this->twitterApi = $this->getContainer()->get(TwitterApiFactory::class)->create();
        $this->logger = $this->getContainer()->get('logger');
        $this->sleepSeconds = intval($input->getOption('sleep'));

        $cache = CacheFactory::create($input->getOption('cache'));
        $this->worker = WorkerFactory::create($cache);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $lastRestart = $this->worker->getTimestampOfLastQueueRestart();
        while (true) {
            try {
                $messages = $this->sqs->getMessages();
                if (count($messages) > 0) {
                    foreach ($messages as $message) {
                        /**@var SignUp $signUp **/
                        $signUp = $this->objectFactory->create($this->sqs->getRawBody($message));
                        $tweet = $signUp->getUsername().' has just submitted the application form!';

                        $this->twitterApi->post('statuses/update', ['status' => $tweet]);
                        $this->sqs->deleteMessage($message);
                    }
                } else {
                    $this->worker->sleep($this->sleepSeconds);
                }

                $this->worker->stopIfNecessary(intval($input->getOption('max_memory')), $lastRestart);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
