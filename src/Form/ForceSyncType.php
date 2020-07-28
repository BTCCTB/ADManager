<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ForceSyncType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'search',
            TextType::class,
            [
                'label' => 'Email or SF ID',
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
    }
}
