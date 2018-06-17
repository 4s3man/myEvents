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
class DeleteUserType extends AbstractType
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
        return 'deleteUser_type';
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
                'validation_groups' => 'deleteUser_default',
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
            'old_password',
            PasswordType::class,
            [
                'required' => false,
                'label' => 'label.oldPassword',
                'constraints' => [
                    new Assert\NotBlank(
                        [
                            'groups' => ['deleteUser_default'],
                        ]
                    ),
                    new CustomAsssert\PasswordMatch(
                        [
                            'groups' => ['deleteUser_default'],
                            'bcrypt' => $options['bcrypt'],
                            'password' => $options['data']['password'],
                        ]
                    ),
                ],
            ]
        );
    }
}
