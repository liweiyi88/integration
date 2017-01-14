<?php

namespace AppBundle\Controller;

use Aws\Sns\Exception\SnsException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Aws\Sns\SnsClient;

class DemoController extends Controller
{
    /**
     * @Route("/sample", name="sample")
     *
     */
    public function indexAction()
    {
        $snsClient = new SnsClient(
            [
                'version' => $this->getParameter('aws_version'),
                'region' => $this->getParameter('aws_region'),
                'credentials' => [
                    'key' => $this->getParameter('aws_key'),
                    'secret' => $this->getParameter('aws_secret')
                ]
            ]
        );

        try {
            $snsClient->publish([
                'Message' => '{"uer_id":12,"email":"julian.li@pepperstone.com","event":"user_created"}',
                'Subject' => 'User Created',
                'TopicArn' => $this->getParameter('aws_sns_topic_arn')
            ]);
        } catch (SnsException $e) {
            dump($e->getMessage());
            die;
        }

        return new Response('User Created Message Published');
    }
}
