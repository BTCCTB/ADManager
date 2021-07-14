<?php

namespace App\Form;

use App\Entity\Officer;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CountryType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OfficerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'user',
                EntityType::class,
                [
                    'label' => 'officer.form.user.label',
                    'placeholder' => 'officer.form.user.placeholder',
                    'class' => User::class,
                    'query_builder' => function (UserRepository $ur) {
                        return $ur->createQueryBuilder('u')
                            ->where('u.roles not like :role')
                            ->setParameter('role', '%ROLE_SUPER_ADMIN%')
                            ->orderBy('u.firstname, u.lastname', 'ASC');
                    },
                    'required' => true,
                    'disabled' => $options['edit'],
                    'attr' => [
                        'class' => 'select2',
                    ],
                ]
            )
            ->add(
                'countries',
                CountryType::class,
                [
                    'label' => 'officer.form.countries.label',
                    'alpha3' => true,
                    'multiple' => true,
                    'required' => true,
                    'attr' => [
                        'class' => 'select2',
                    ],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Officer::class,
            'edit' => false,
        ]);
    }
}
