<?php

namespace AppBundle\SignUp;

use AppBundle\Entity\SignUp;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class SignUpHandler
{
    private $entityManager;
    private $dispatcher;

    public function __construct(
        EntityManagerInterface $entityManager,
        EventDispatcherInterface $dispatcher
    ) {
        $this->entityManager = $entityManager;
        $this->dispatcher = $dispatcher;
    }

    public function persist(SignUp $signUp)
    {
        $this->entityManager->persist($signUp);
        $this->entityManager->flush();

        $this->dispatcher->dispatch(SignUpEvents::SIGNUP_COMPLETED, new SignUpEvent($signUp));
    }
}
