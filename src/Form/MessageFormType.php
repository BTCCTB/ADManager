<?php

namespace App\Form;

use App\Entity\MessageLog;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MessageFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'recipient',
                ChoiceType::class,
                [
                    'label' => 'message.form.label.recipient',
                    'choices' => MessageLog::recipientList(),
                    'multiple' => true,
                    'attr' => [
                        'class' => 'select2',
                    ],
                ]
            )
            ->add(
                'multilanguage',
                CheckboxType::class,
                [
                    'label' => 'message.form.label.multilanguage',
                    'attr' => [
                        'class' => 'multi_check',
                    ],
                    'required' => false,
                ]
            )
            ->add(
                'message',
                TextareaType::class,
                [
                    'label' => 'message.form.label.message',
                    'attr' => [
                        'rows' => 4,
                    ],
                ]
            )
            ->add(
                'messageFr',
                TextareaType::class,
                [
                    'label' => 'message.form.label.message.fr',
                    'attr' => [
                        'rows' => 4,
                        'class' => 'multi',
                    ],
                    'required' => false,
                ]
            )
            ->add(
                'messageNl',
                TextareaType::class,
                [
                    'label' => 'message.form.label.message.nl',
                    'attr' => [
                        'rows' => 4,
                        'class' => 'multi',
                    ],
                    'required' => false,
                ]
            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MessageLog::class,
        ]);
    }
}
