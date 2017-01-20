<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Aws\Sns\Exception\SnsException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
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

    /**
     * @Route("/", name="user_registration")
     */
    public function registerAction(Request $request)
    {
        $form = $this->createForm(UserType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $user = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $snsClient = $this->get('aws_factory')->createClient('sns');

            try {
                $snsClient->publish([
                    'Message' => $this->get('jms_serializer')->serialize($user, 'json'),
                    'Subject' => $this->getParameter('aws_user_created_subject'),
                    'TopicArn' => $this->getParameter('aws_user_created_arn')
                ]);
            } catch (SnsException $e) {
                dump($e);
                //put exception into log or send email.
            }

            $this->addFlash('success', 'User created!');

            return $this->redirectToRoute('user_registration');
        }

        return $this->render('demo/user_registration.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
