<?php
namespace AppBundle\Controller;

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
            $user = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->get('aws.sns.client')->publish(
                $this->get('jms_serializer')->serialize($user, 'json'),
                $this->getParameter('aws_user_created_subject'),
                $this->getParameter('aws_user_created_arn')
            );

            $this->addFlash('success', 'User created!');
            return $this->redirectToRoute('user_registration');
        }

        return $this->render('demo/user_registration.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
