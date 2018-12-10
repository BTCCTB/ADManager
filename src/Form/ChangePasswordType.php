<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'current_password',
            PasswordType::class,
            [
                'label' => 'change.password.form.current.password.label',
                'attr' => ['class' => 'password-field'],
            ]
        );

        $builder->add(
            'password',
            RepeatedType::class,
            [
                'type' => PasswordType::class,
                'invalid_message' => 'change.password.form.new.password.invalid',
                'options' => [
                    'attr' => [
                        'class' => 'password-field',
                    ],
                ],
                'required' => true,
                'first_options' => [
                    'label' => 'change.password.form.new.password.label',
                ],
                'second_options' => [
                    'label' => 'change.password.form.repeat.password.label',
                ],
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
    }
}
