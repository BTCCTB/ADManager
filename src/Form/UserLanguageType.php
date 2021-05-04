<?php

namespace App\Form;

use App\Entity\UserLanguage;
use Bis\Service\BisPersonView;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserLanguageType extends AbstractType
{

    /**
     * @var BisPersonView
     */
    private $bisPersonView;

    public function __construct(BisPersonView $bisPersonView)
    {
        $this->bisPersonView = $bisPersonView;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'userId',
                ChoiceType::class,
                [
                    'label' => 'user.language.form.label.user',
                    'choices' => $this->bisPersonView->userChoices(),
                    'choice_translation_domain' => false,
                    'multiple' => false,
                    'attr' => [
                        'class' => 'select2',
                    ],
                    'required' => true,
                ]
            )
            ->add(
                'language',
                ChoiceType::class,
                [
                    'label' => 'user.language.form.label.language',
                    'choices' => UserLanguage::languageChoices(),
                    'multiple' => false,
                    'attr' => [
                        'class' => 'select2',
                    ],
                    'required' => true,
                ]
            )

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserLanguage::class
        ]);
    }
}
