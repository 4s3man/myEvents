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
class LinkUserCalendarType extends AbstractType
{
    /**
     *
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'link_user_calendar_type';
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
                'validation_groups' => 'link_user_calendar_default',
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
        $builder->add(
            'email',
            EmailType::class,
            [
                'label' => 'label.email',
                'required' => true,
                'constraints' => [
                    new Assert\Email(
                        [
                            'groups' => [ 'link_user_calendar_default' ],
                        ]
                    ),
                    new CustomAsssert\NotUniqueness(
                        [
                        'groups' => [ 'link_user_calendar_default' ],
                        'repository' => isset($options['repository']) ? $options['repository'] : null,
                        'uniqueColumn' => 'email',
                        ]
                    ),
                    new Assert\NotBlank(
                        [
                             'groups' => ['link_user_calendar_default'],
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'user_role',
            ChoiceType::class,
            [
                'label' => 'label.user_choice_type',
                'choices' => [
                    'calendar editor' => 'calendar_editor',
                    'calendar admin' => 'calendar_admin',
                ],
            ]
        );
    }
}
