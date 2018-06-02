<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 20.05.18
 * Time: 21:06
 */

namespace Form;

use Form\Helpers\PopularAssertGroups;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CalendarType used by form builder
 */
class EventType extends AbstractType
{
    protected $year = null;

    /**
     * @var PopularAssertGroups|null
     */
    private $popularAsserts = null;
    /**
     * CalendarType constructor.
     */
    public function __construct()
    {
        $this->popularAsserts = new PopularAssertGroups();
        $this->year = date('y');
    }

    /**
     * Builds form
     *
     * @param FormBuilderInterface $builder
     *
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'title',
            TextType::class,
            [
                'label' => 'label.event_title',
                'required' => true,
                'constraints' => $this->popularAsserts->usernameAsserts(['event_default']),
            ]
        );

        $builder->add(
            'content',
            TextType::class,
            [
                'label' => 'label.content',
                'constraints' => [
                    new Assert\Regex(
                        [
                          'groups' => 'event_default',
                          'pattern' => '/^([\s\n\p{L}0-9]+[,\s\n!?\)\(\.:-]*[\s\n\s\p{L}!?0-9]*)*$/u',
                            ]
                    ),
                    new Assert\Length(
                        [
                            'max' => 700,
                        ]
                    ),
                ],
            ]
        );
        //TODO jak zmienić domyślną wartość na obecny czas
        $builder->add(
            'start',
            DateTimeType::class,
            [
                'label' => 'label.event_start',
                'required' => true,
                'input' => 'string',
                'constraints' => [
                    new Assert\DateTime(
                        [
                            'groups' => 'event_default',
                        ]
                    ),
                    new Assert\NotBlank(
                        [
                            'groups' => 'event_default',
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'end',
            DateTimeType::class,
            [
                'label' => 'label.event_end',
                'required' => true,
                'input' => 'string',
                'constraints' => [
                    new Assert\DateTime(
                        [
                            'groups' => 'event_default',
                        ]
                    ),
                    new Assert\NotBlank(
                        [
                            'groups' => 'event_default',
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'type',
            ChoiceType::class,
            [
                'label' => 'lebel.event_type',
                'choices' => [
                    'label.recurrent_null' => null,
                    'label.recurrent_daily' => 'daily',
                    'label.recurrent_weekly' => 'weekly',
                    'label.recurrent_monthly' => 'monthly',
                ],
                'constraints' => [
                    new Assert\Choice(
                        [
                          'daily',
                          'weekly',
                          'monthly',
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'until',
            DateTimeType::class,
            [
                'label' => 'label.event_until',
                'input' => 'string',
                'constraints' => [
                    new Assert\DateTime(
                        [
                            'groups' => 'event_default',
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'cost',
            IntegerType::class,
            [
                'label' => 'label.events_cost',
                'required' => false,
                'constraints' => [
                    new Assert\Regex(
                        [
                            'groups' => 'event_default',
                            'pattern' => '/[1-9]{1}[0-9]*/',
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'seats',
            IntegerType::class,
            [
                'label' => 'label.events_seats',
                'required' => false,
                'constraints' => [
                    new Assert\Regex(
                        [
                            'groups' => 'event_default',
                            'pattern' => '/[1-9]{1}[0-9]*/',
                        ]
                    ),
                ],
            ]
        );

        //TODO PYTANIE jak najlepiej zrobić żeby end date nie mógł być ustawiony przed start_date
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
                'validation_groups' => 'event_default',
            ]
        );
    }

    /**
     * Returns block prefixs
     *
     * @return null|string
     */
    public function getBlockPrefix()
    {
        return 'event_type';
    }
}
