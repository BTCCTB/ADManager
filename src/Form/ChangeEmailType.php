<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ChangeEmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'new_email',
            TextType::class,
            [
                'label' => 'New email',
            ]
        );

        $builder->add(
            'keep_proxy',
            CheckboxType::class,
            [
                'label' => 'Keep current proxy as secondary',
                'required' => false,
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
    }
}
