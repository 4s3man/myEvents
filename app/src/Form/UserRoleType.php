<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 08.05.18
 * Time: 20:33
 */

namespace Form;

use Form\Helpers\PopularAssertGroups;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Validator\Constraints as CustomAsssert;

/**
 * Class SignUpType
 *
 * Used by creatBuilder funcition in controller
 */
class UserRoleType extends AbstractType
{
    /**
     *
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'user_role_type';
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
                'repository' => null,
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
        //TODO wrzucić to do add
        $builder->add(
            'user_role',
            ChoiceType::class,
            [
                'label' => 'label.user_choice_type',
                'choices' => [
                    'editor' => 'EDITOR',
                    'admin' => 'ADMIN',
                ],
                'constraints' => [
                    new Assert\Choice(
                        [
                          'choices' => [
                              'EDITOR',
                              'ADMIN',
                          ],
                        ]
                    ),
                ],
            ]
        );
    }
}