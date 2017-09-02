<?php
namespace AppBundle\Controller;

use AppBundle\Form\SignUpType;
use AppBundle\SignUp\SignUpHandler;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

class SignUpController extends Controller
{
    /**
     * @Route("/", name="user_registration")
     */
    public function registerAction(Request $request, SignUpHandler $signUpHandler)
    {
        $form = $this->createForm(SignUpType::class);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $signUp = $form->getData();
            $signUp->setIp($request->getClientIp());
            $signUpHandler->persist($signUp);

            $this->addFlash(
                'notice',
                'Wait a few seconds and your will see a new tweet in the twitter widget.'
            );

            $this->addFlash('success',
                'Form Submitted successfully!'
            );
            return $this->redirectToRoute('user_registration');
        }

        return $this->render('demo/user_registration.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
