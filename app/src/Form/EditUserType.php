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
class EditUserType extends AbstractType
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
     *
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'editUser_type';
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
                'validation_groups' => 'editUser_default',
                'repository' => null,
                'bcrypt' => null,
            ]
        );
    }

    /**
     *
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
                'constraints' => $this->popularAsserts->name([ 'editUser_default' ]),
            ]
        );
        $builder->add(
            'last_name',
            TextType::class,
            [
                'label' => 'label.last_name',
                'required' => true,
                'attr' => [],
                'constraints' => $this->popularAsserts->name([ 'editUser_default' ]),
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
                            'groups' => [ 'editUser_default' ],
                        ]
                    ),
                    new CustomAsssert\NotItselfUniqueness(
                        [
                        'groups' => [ 'editUser_default' ],
                        'repository' => isset($options['repository']) ? $options['repository'] : null,
                        'itself' => $options['data']['email'],
                        'uniqueColumn' => 'email',
                        ]
                    ),
                    new Assert\NotBlank(
                        [
                             'groups' => ['editUser_default'],
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
                    $this->popularAsserts->slug(['editUser_default']),
                    [
                        new CustomAsssert\NotItselfUniqueness(
                            [
                            'groups' => [ 'editUser_default' ],
                            'repository' => isset($options['repository']) ? $options['repository'] : null,
                            'itself' => $options['data']['login'],
                            'uniqueColumn' => 'login',
                            ]
                        ),
                        new Assert\NotBlank(
                            [
                                'groups' => ['editUser_default'],
                            ]
                        ),
                    ]
                ),
            ]
        );
        $builder->add(
            'new_password',
            RepeatedType::class,
            [
                'type' => PasswordType::class,
                'required' => false,
                'first_options' => [
                    'label' => 'label.password',
                ],
                'second_options' => [
                    'label' => 'label.retype_password',
                ],
            ]
        );
        $builder->add(
            'old_password',
            PasswordType::class,
            [
                'required' => false,
                'label' => 'label.old_password',
                'constraints' => [
                    new Assert\NotBlank(
                        [
                            'groups' => ['editUser_default'],
                        ]
                    ),
                    new CustomAsssert\PasswordMatch(
                        [
                            'groups' => ['editUser_default'],
                            'bcrypt' => $options['bcrypt'],
                            'password' => $options['data']['password'],
                        ]
                    ),
                ],
            ]
        );
    }
}
