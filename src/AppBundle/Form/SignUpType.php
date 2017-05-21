<?php

namespace AppBundle\Form;

use AppBundle\Entity\SignUp;
use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SignUpType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', TextType::class, array(
                'label' => 'Username',
                'label_attr' => ['class' => 'mdc-textfield__label'],
                'required' => true
            ))
            ->add('email', EmailType::class, array(
                'label' => 'Email',
                'label_attr' => ['class' => 'mdc-textfield__label'],
                'required' => true
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => SignUp::class
        ]);
    }

    public function getName()
    {
        return 'user';
    }
}
