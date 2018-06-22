<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 20.05.18
 * Time: 21:06
 */

namespace Form;

use Form\DataTransformer\BooleanDataTransformer;
use Form\DataTransformer\DateDataTransformer;
use Form\DataTransformer\MainImgDataTransformer;
use Form\DataTransformer\MediaToChoicesDataTransformer;
use Form\Helpers\Regexps;
use Repositiory\MediaRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Validator\Constraints as CustomAssert;
use Form\DataTransformer\TagDataTransformer;

/**
 * Class CalendarType used by form builder
 */
class EventType extends AbstractType
{

    /**
     *
     * @var Regexps|null
     */
    private $popularAsserts = null;
    /**
     * CalendarType constructor.
     */
    public function __construct()
    {
        $this->popularAsserts = new Regexps();
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
                'constraints' => [
                    new Assert\Regex(
                        [
                            'groups' => ['event_default', 'event_edit'],
                            'pattern' => $this->popularAsserts->getContentRegexp(),
                        ]
                    ),
                    new Assert\Length(
                        [
                            'groups' => ['event_default', 'event_edit'],
                            'max' => 128,
                        ]
                    ),
                ],
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
                          'groups' => ['event_default', 'event_edit'],
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

        $builder->add(
            'start',
            DateTimeType::class,
            [
                'label' => 'label.event_start',
                'required' => true,
                'attr' => ['class' => 'js-datepicker'],
                'years' => [
                  date('Y'),
                  date('Y', strtotime('+ 1 year')),
                  date('Y', strtotime('+ 2 year')),
                ],
                'constraints' => [
                    new Assert\DateTime(
                        [
                            'groups' => 'event_default',
                        ]
                    ),
                    new Assert\NotBlank(
                        [
                            'groups' => ['event_default', 'event_edit'],
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
                'attr' => ['class' => 'js-datepicker'],
                'years' => [
                    date('Y'),
                    date('Y', strtotime('+ 1 year')),
                    date('Y', strtotime('+ 2 year')),
                ],
                'constraints' => [
                    new Assert\DateTime(
                        [
                            'groups' => ['event_default', 'event_edit'],
                        ]
                    ),
                    new Assert\NotBlank(
                        [
                            'groups' => ['event_default', 'event_edit'],
                        ]
                    ),
                    new CustomAssert\DateRange(
                        [
                            'groups' => ['event_default'],
                            'min' => isset($options['data']['start']) ? $options['data']['start'] : date('Y-m-d G:i'),
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'cost',
            IntegerType::class,
            [
                'label' => 'label.event_cost',
                'required' => false,
                'constraints' => [
                    new Assert\Range(
                        [
                            'groups' => ['event_default', 'event_edit'],
                            'min' => 0,
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'seats',
            IntegerType::class,
            [
                'label' => 'label.event_seats',
                'required' => false,
                'constraints' => [
                    new Assert\Range(
                        [
                            'groups' => ['event_default', 'event_edit'],
                            'min' => 0,
                        ]
                    ),
                ],
            ]
        );

        $builder->add(
            'sign_up',
            CheckboxType::class,
            [
              'label' => 'label.sign_up',
              'required' => false,
              'value' => true,
            ]
        );

        $builder->add(
            'tags',
            TextType::class,
            [
              'label' => 'label.tags_add',
              'required' => false,
              'attr' => [
                  'length' => 128,
              ],
            ]
        );

        $media = $this->getMedia($options['media_repository'], $options['calendarId'], $options['userId']);
        $choices = $this->mediaToChoices($media);
        $builder->add(
            'main_img',
            MainImgType::class,
            [
              'label' => 'label.main_img',
              'choices' => $choices,
              'media' => $media,
              'constraints' => [
                  new Assert\Choice(
                      [
                          'groups' => ['event_default', 'event_edit'],
                          'choices' => $choices,
                      ]
                  ),
              ],
            ]
        );

        $builder->get('sign_up')->addModelTransformer(
            new BooleanDataTransformer()
        );

        $builder->get('start')->addModelTransformer(
            new DateDataTransformer()
        );
        $builder->get('end')->addModelTransformer(
            new DateDataTransformer()
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
                'validation_groups' => ['event_default', 'event_edit'],
                'event_repository' => null,
                'tag_repository' => null,
                'media_repository' => null,
                'userId' => null,
                'calendarId' => null,
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

    /**
     * @param mixed $media gotten from database
     *
     * @return array
     */
    private function mediaToChoices($media)
    {
        $choices = [];
        foreach ($media as $medium) {
            $choices[$medium['title']] = $medium['id'];
        }

        return $choices;
    }

    /**
     * @param MediaRepository $repository
     *
     * @param int             $calendarId
     * @param int             $userId
     *
     * @return array
     */
    private function getMedia(MediaRepository $repository, $calendarId, $userId)
    {
        return $repository->findAllForUserAndCalendar($userId, $calendarId);
    }
}
