<?php
namespace AppBundle\Controller;

use AppBundle\Entity\Queue;
use AppBundle\Entity\User;
use AppBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

class DemoController extends Controller
{
    /**
     * @Route("/", name="user_registration")
     */
    public function registerAction(Request $request)
    {
        $form = $this->createForm(UserType::class);

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

            $this->get('aws.sns.helper')->publish(
                $this->get('jms_serializer')->serialize($user, 'json'),
                $this->getParameter('aws_user_created_subject'),
                $this->getParameter('aws_user_created_arn')
            );

            $this->addFlash('success', 'Form Submitted successfully!');
            return $this->redirectToRoute('user_registration');
        }

        return $this->render('demo/user_registration.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
