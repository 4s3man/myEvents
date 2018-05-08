<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 08.05.18
 * Time: 20:33
 */

namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class RegisterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'first_name',
            TextType::class,
            [
                'label' => 'label.first_name',
                'required' => true,
                'attr' => [],
                'constraints' => [

                ]
            ]
        );
        $builder->add(
            'nazwisko',
            TextType::class,
            [
                'label' => 'label.last_name',
                'required' => true,
                'attr' => [],
                'constraints' => [

                ],
            ]
        );
        $builder->add(
            'email',
            EmailType::class,
            [
                'label' => 'label.email',
                'constraints' => [

                ]
            ]
        );
        $builder->add(
            'login',
            TextType::class,
            [
                'label' => 'label.login',
            ]
        );
        $builder->add(
            'password',
            PasswordType::class,
            [
                'label' => 'label.password',
                'constraints' => [

                ]
            ]
        );
        $builder->add(
            'retype_password',
            PasswordType::class,
            [
                'label' => 'label.retype_password',
                'constraints' => [

                ]
            ]
        );

        $builder->add(
            'submit',
            SubmitType::class,
            [
                'label' => 'label.register',
            ]
        );
    }

    public function getBlockPrefix()
    {
        return 'register_type';
    }
}
