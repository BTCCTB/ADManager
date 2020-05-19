<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExternalFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'firstname',
                TextType::class,
                [
                    'label' => 'external.form.label.firstname',
                    'required' => true,
                ]
            )
            ->add(
                'lastname',
                TextType::class,
                [
                    'label' => 'external.form.label.lastname',
                    'required' => true,
                ]
            )
            ->add(
                'login',
                TextType::class,
                [
                    'label' => 'external.form.label.login',
                    'attr' => ['class' => 'js-login'],
                    'help' => 'external.form.help.login',
                    'required' => true,
                ]
            )
            ->add(
                'phone',
                TextType::class,
                [
                    'label' => 'external.form.label.phone',
                    'required' => false,
                    'help' => 'external.form.help.phone',
                ]
            )
            ->add(
                'jobTitle',
                TextType::class,
                [
                    'label' => 'external.form.label.jobTitle',
                    'required' => false,
                ]
            )
            ->add(
                'company',
                TextType::class,
                [
                    'label' => 'external.form.label.company',
                    'required' => false,
                ]
            )
            ->add(
                'expirationDate',
                DateType::class,
                [
                    'label' => 'external.form.label.expirationDate',
                    'widget' => 'single_text',
                    'html5' => false,
                    'attr' => ['class' => 'js-datepicker'],
                    'required' => true,
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
    }
}
