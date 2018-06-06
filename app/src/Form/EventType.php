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
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
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
        //TODO dodać ograniczenia event ma się kończyć po tym jak się rozpocznie
        //dodać media
        //dodać tagi
        $builder->add(
            'title',
            TextType::class,
            [
                'label' => 'label.event_title',
                'required' => true,
                'constraints' => $this->popularAsserts->title(['event_default']),
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
                          'pattern' => $this->popularAsserts->getContentRegexp(),
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
        //TODO pytanie jak zmienić domyślną wartość na obecny czas

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
                            'groups' => ['event_default'],
                        ]
                    ),
                    new Assert\NotBlank(
                        [
                            'groups' => ['event_default'],
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
                    new Assert\Type(
                        [
                            'groups' => ['event_default'],
                            'type' => 'integer',
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
                    new Assert\Type(
                        [
                            'groups' => ['event_default'],
                            'type' => 'integer',
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'signUp',
            CheckboxType::class,
            [
              'label' => 'label.sign_up',
              'required' => false,
            ]
        );

        $builder->add(
            'tags',
            TextType::class,
            [
              'label' => 'label.tags',
              'required' => false,
              'attr' => [
                  'length' => 128,
              ],
            ]
        );

        $builder->get('tags')->addModelTransformer(
            new TagDataTransformer($options['tag_repository'])
        );
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
                'event_repository' => null,
                'tag_repository' => null,
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
