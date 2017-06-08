<?php
namespace AppBundle\Controller;

use AppBundle\Entity\Content;
use AppBundle\Entity\SignUp;
use AppBundle\Entity\User;
use AppBundle\Form\SignUpType;
use AppBundle\Model\Command;
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
    private $entityManager;
    private $serializer;
    private $sqs;

    public function __construct(Serializer $serializer, EntityManager $entityManager, SQS $sqs)
    {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->sqs = $sqs;
    }

    /**
     * @Route("/", name="user_registration")
     */
    public function registerAction(Request $request)
    {
        $form = $this->createForm(SignUpType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var SignUp $signUp */
            $signUp = $form->getData();

            $this->entityManager->persist($signUp);
            $this->entityManager->flush();

            //The Queue entity is not necessary. It is just used to make it easier to show the process behind the sense.
            $this->persistContent($signUp->getEmail(), $signUp->getUsername(), $this->getParameter('confirmation_queue'));
            $this->persistContent($signUp->getEmail(), $signUp->getUsername(), $this->getParameter('mailchimp_queue'));

            //push message to the queue
            $confirmationEmail = $this->createConfirmationEmail($signUp->getEmail(), $signUp->getUsername());
            $this->push($confirmationEmail, $this->getParameter('confirmation_queue'));

            $mailchimp = $this->createMailchimp($signUp->getEmail(), $signUp->getUsername());
            $this->push($mailchimp, $this->getParameter('mailchimp_queue'));

            $this->addFlash('success', 'Form Submitted successfully!');
            return $this->redirectToRoute('user_registration');
        }

        $confirmationQueue = $this->getDoctrine()->getRepository(Content::class)->findBy(['queue' => $this->getParameter('confirmation_queue')]);
        $mailchimpQueue = $this->getDoctrine()->getRepository(Content::class)->findBy(['queue' => $this->getParameter('mailchimp_queue')]);

        return $this->render('demo/user_registration.html.twig', [
            'form' => $form->createView(),
            'confirmation_queue' => $confirmationQueue,
            'mailchimp_queue' => $mailchimpQueue
        ]);
    }

    private function persistContent(string $email, string $username, string $queueName)
    {
        $queue = new Content();
        $queue->setQueue($queueName);
        $queue->setEmail($email);
        $queue->setUsername($username);

        $this->entityManager->persist($queue);
        $this->entityManager->flush();
    }


    private function createConfirmationEmail(string $email, string $username)
    {
        $confirmationEmail = new ConfirmationEmail();
        $confirmationEmail->setEmail($email);
        $confirmationEmail->setUsername($username);

        return $confirmationEmail;
    }

    private function createMailchimp(string $email, string $username)
    {
        $mailchimp = new Mailchimp();
        $mailchimp->setEmail($email);
        $mailchimp->setUsername($username);

        return $mailchimp;
    }

    private function push(Command $command, string $queue)
    {
        $message = $this->serializer->serialize($command, 'json');
        $this->sqs->push($message, $queue);
    }
}
