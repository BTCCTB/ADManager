<?php

namespace AuthBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * @author Damien Lagae <damienlagae@gmail.com>
 */
class LoginForm extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            '_username',
            EmailType::class,
            array(
                'label' => 'Username',
                'attr' => [
                    'placeholder' => "lastname.firstname@enabel.be",
                ],
            )
        );
        $builder->add(
            '_password',
            PasswordType::class,
            array(
                'label' => 'Password',
            )
        );
        $builder->add(
            '_remember',
            CheckboxType::class,
            array(
                'label' => 'Remember',
                'required' => false,
            )
        );
    }
}
