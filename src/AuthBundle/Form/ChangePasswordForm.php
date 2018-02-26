<?php

namespace AuthBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangePasswordForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'current_password',
            PasswordType::class,
            [
                'label' => 'Current password',
                'attr' => ['class' => 'password-field'],
            ]
        );

        $builder->add(
            'password',
            RepeatedType::class,
            [
                'type' => PasswordType::class,
                'invalid_message' => 'The new password fields must match.',
                'options' => [
                    'attr' => [
                        'class' => 'password-field',
                    ],
                ],
                'required' => true,
                'first_options' => [
                    'label' => 'New password',
                ],
                'second_options' => [
                    'label' => 'Repeat new password',
                ],
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
    }

    public function getBlockPrefix()
    {
        return 'auth_bundle_change_password_form';
    }
}
