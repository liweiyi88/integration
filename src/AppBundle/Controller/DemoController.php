<?php
namespace AppBundle\Controller;

use AppBundle\Entity\Content;
use AppBundle\Entity\SignUp;
use AppBundle\Entity\User;
use AppBundle\Form\SignUpType;
use AppBundle\Queue\SQS;
use AppBundle\Enum\Queue;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
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
}
