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
use AppBundle\Enum\Queue;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\SerializerInterface;

class DemoController extends Controller
{
    private $entityManager;
    private $serializer;
    private $sqs;

    public function __construct(SerializerInterface $serializer, EntityManagerInterface $entityManager, SQS $sqs)
    {
        $this->serializer = $serializer;
        $this->entityManager = $entityManager;
        $this->sqs = $sqs;
    }

    /**
     * @Route("/content/get/{queue}", name="get_content")
     */
    public function getJsonContent($queue)
    {
        $contents = $this->getDoctrine()->getRepository(Content::class)->findBy(['queue' => $queue]);

        $contentsArray = [];

        foreach ($contents as $content) {
            $contentsArray[$content->getId()] = $content->getUsername();
        }

        return new JsonResponse($contentsArray);
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

            //push message to the queue
            $confirmationEmail = new ConfirmationEmail($signUp->getEmail(), $signUp->getUsername());
            $this->push($confirmationEmail, Queue::CONFIRMATION);

            $mailchimp = new Mailchimp($signUp->getEmail(), $signUp->getUsername());
            $this->push($mailchimp, Queue::MAILCHIMP);

            //The Content entity is not necessary. It is just used to make it easier to show the process behind the sense.
            $this->persistContent($signUp->getEmail(), $signUp->getUsername(), Queue::CONFIRMATION);
            $this->persistContent($signUp->getEmail(), $signUp->getUsername(), Queue::MAILCHIMP);

            $this->addFlash('success', 'Form Submitted successfully!');
            return $this->redirectToRoute('user_registration');
        }

        $confirmationQueue = $this->getDoctrine()->getRepository(Content::class)->findBy(['queue' => Queue::CONFIRMATION]);
        $mailchimpQueue = $this->getDoctrine()->getRepository(Content::class)->findBy(['queue' => Queue::MAILCHIMP]);

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

    private function push(Command $command, string $queue)
    {
        $message = $this->serializer->serialize($command, 'json');
        $this->sqs->push($message, $queue);
    }
}
