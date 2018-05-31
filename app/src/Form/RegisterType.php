<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 08.05.18
 * Time: 20:33
 */

namespace Form;

use Form\Helpers\PopularAssertGroups;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
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
     * Asserts helper
     *
     * @var PopularAssertGroups|null
     */
    private $popularAsserts = null;

    /**
     * RegisterType constructor.
     */
    public function __construct()
    {
        $this->popularAsserts = new PopularAssertGroups();
    }
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
                'constraints' => $this->popularAsserts->textAsserts(),
            ]
        );
        $builder->add(
            'last_name',
            TextType::class,
            [
                'label' => 'label.last_name',
                'required' => true,
                'attr' => [],
                'constraints' => $this->popularAsserts->textAsserts(),
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
                    new CustomAsssert\Uniqueness(
                        [
                        'groups' => [ 'register_default' ],
                        'repository' => isset($options['repository']) ? $options['repository'] : null,
                        'uniqueColumn' => 'email',
                        ]
                    ),
                    new Assert\NotBlank(
                        [
                             'groups' => ['register_default'],
                        ]
                    ),
                ],
            ]
        );
        $builder->add(
            'login',
            TextType::class,
            [
                'label' => 'label.login',
                'required' => true,
                'constraints' => array_merge(
                    $this->popularAsserts->usernameAsserts(),
                    [
                        new CustomAsssert\Uniqueness(
                            [
                            'groups' => [ 'register_default' ],
                            'repository' => isset($options['repository']) ? $options['repository'] : null,
                            'uniqueColumn' => 'login',
                            ]
                        ),
                    ]
                ),
            ]
        );
        $builder->add(
            'password',
            RepeatedType::class,
            [
                'type' => PasswordType::class,
                'required' => true,
                'first_options' => [
                    'label' => 'label.password',
                ],
                'second_options' => [
                    'label' => 'label.retype_password',
                ],
            ]
        );
    }
}
