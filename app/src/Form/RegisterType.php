<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 08.05.18
 * Time: 20:33
 */

namespace Form;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Validator\Constraints as CustomAsssert;


/**
 * Class RegisterType
 *
 * Used by creatBuilder funcition in controller
 */
class RegisterType extends AbstractType
{
    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'register_type';
    }

    /**
     * Set options
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'validation_groups' => 'register_default',
                'repository' => null,
            ]
        );
    }

    /**
     * @inheritdoc
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add(
            'first_name',
            TextType::class,
            [
                'label' => 'label.first_name',
                'required' => true,
                'attr' => [],
                'constraints' => $this->textAsserts(),
            ]
        );
        $builder->add(
            'last_name',
            TextType::class,
            [
                'label' => 'label.last_name',
                'required' => true,
                'attr' => [],
                'constraints' => $this->textAsserts(),
            ]
        );
        $builder->add(
            'email',
            EmailType::class,
            [
                'label' => 'label.email',
                'required' => true,
                'constraints' => [
                    new Assert\Email(
                        [
                            'groups' => [ 'register_default' ],
                        ]
                    ),
                    new CustomAsssert\Uniqueness([
                        'groups' => [ 'register_default' ],
                        'repository' => isset($options['repository']) ? $options['repository'] : null,
                    ]),
                ],
            ]
        );
        $builder->add(
            'username',
            TextType::class,
            [
                'label' => 'label.login',
                'required' => true,
                'constraints' => $this->textAsserts(),
            ]
        );
        $builder->add(
            'password',
            PasswordType::class,
            [
                'label' => 'label.password',
                'required' => true,
                'constraints' => $this->passwordAsserts(),
            ]
        );
        $builder->add(
            'retype_password',
            PasswordType::class,
            [
                'label' => 'label.retype_password',
                'required' => true,
                'constraints' => $this->passwordAsserts(),
            ]
        );

        $builder->add(
            'submit',
            SubmitType::class,
            [
                'label' => 'label.register',
            ]
        );

//        $builder->addEventListener(
//            FormEvents::PRE_SUBMIT,
//            function (FormEvent $event) {
//                $form = $event->getForm();
//                $data = $event->getData();
//                unset($data['retype_password']);
//                $event->setData($data);
//            }
//        );
    }

    /**
     * Asserty spięte do jednej tablicy może potem
     * możnaby je wyrzucić do osobnego obiektu jakby już ich
     * było sporo powtarzających się
     *
     * @param array $groups validation groups
     *
     * @return array
     */
    private function textAsserts($groups = ['register_default'])
    {
        return [
            new Assert\NotBlank([
                'groups' => $groups,
            ]),
            new Assert\Regex(
                [
                    'groups' => $groups,
                    'pattern' => "/^[A-Za-zżźćńółęąśŻŹĆĄŚĘŁÓŃ]*$/",
                ]
            ),
            new Assert\Length([
                'groups' => $groups,
                'max' => 30,
            ]),
        ];
    }

    /**
     * Asserty spięte do jednej tablicy może potem
     * możnaby je wyrzucić do osobnego obiektu jakby już ich
     * było sporo powtarzających się
     *
     * @param array $groups validation_groups
     *
     * @return array
     */
    private function passwordAsserts($groups = ['register_default'])
    {
        return [
            new Assert\NotBlank(
                [
                    'groups' => $groups,
                ]
            ),
        ];
    }
}
