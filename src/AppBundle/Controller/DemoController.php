<?php
namespace AppBundle\Controller;

use AppBundle\Entity\Queue;
use AppBundle\Entity\User;
use AppBundle\Form\SignUpType;
use AppBundle\Model\ConfirmationEmail;
use AppBundle\Model\Mailchimp;
use AppBundle\Queue\SQS;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Serializer;

class DemoController extends Controller
{
    /**
     * @Route("/", name="user_registration")
     */
    public function registerAction(Request $request, SQS $sqs, Serializer $serializer)
    {
        $form = $this->createForm(SignUpType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);

            //The Queue entity is not necessary. It is just used to make it easier to show the process behind the sense.
            $confirmationQueue = new Queue();
            $confirmationQueue->setName('confirmation');
            $confirmationQueue->setUsername($user->getUsername());
            $confirmationQueue->setEmail($user->getEmail());

            $mailchimpQueue = new Queue();
            $mailchimpQueue->setName('mailchimp');
            $mailchimpQueue->setUsername($user->getUsername());
            $mailchimpQueue->setEmail($user->getEmail());

            $em->persist($confirmationQueue);
            $em->persist($mailchimpQueue);
            $em->flush();

            $this->pushConfirmationMessage($user->getEmail(), $user->getUsername(), $sqs, $serializer);
            $this->pushMailChimpMessage($user->getEmail(), $user->getUesrname(), $sqs, $serializer);

            $this->addFlash('success', 'Form Submitted successfully!');
            return $this->redirectToRoute('user_registration');
        }

        $confirmationQueue = $this->getDoctrine()->getRepository('AppBundle:Queue')->findBy(['name' => Queue::CONFIRMATION]);
        $mailchimpQueue = $this->getDoctrine()->getRepository('AppBundle:Queue')->findBy(['name' => Queue::MAILCHIMP]);

        return $this->render('demo/user_registration.html.twig', [
            'form' => $form->createView(),
            'confirmation_queue' => $confirmationQueue,
            'mailchimp_queue' => $mailchimpQueue
        ]);
    }

    private function persistConfirmationQueue(string $email, string $username, EntityManager $entityManager)
    {


    }


    private function pushConfirmationMessage(string $email, string $username, SQS $sqs, Serializer $serializer)
    {
        $confirmationEmail = new ConfirmationEmail();
        $confirmationEmail->setEmail($email);
        $confirmationEmail->setUsername($username);
        $confirmationMessage = $serializer->serialize($confirmationEmail,'json');
        $sqs->push($confirmationMessage, $this->getParameter('confirmation_queue'));
    }

    private function pushMailChimpMessage(string $email, string $username, SQS $sqs, Serializer $serializer)
    {
        $mailchimp = new Mailchimp();
        $mailchimp->setEmail($email);
        $mailchimp->setUsername($username);
        $mailchimpMessage = $serializer->serialize($mailchimp, 'json');
        $sqs->push($mailchimpMessage, $this->getParameter('mailchimp_queue'));
    }

}
