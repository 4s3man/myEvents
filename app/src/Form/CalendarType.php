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
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CalendarType used by form builder
 */
class CalendarType extends AbstractType
{
    /**
     *
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
        //TODO zrobic klase z regexpami a poza tym nie chowac tych constraintow
        $builder->add(
            'title',
            TextType::class,
            [
                'label' => 'label.calendar_title',
                'required' => true,
                'constraints' => $this->popularAsserts->title(['calendar_default']),
            ]
        );
        $builder->add(
            'description',
            TextType::class,
            [
                'label' => 'label.calendar_description',
                'required' => false,
                'constraints' => [
                    new Assert\Regex(
                        [
                            'groups' => 'calendar_default',
                            'pattern' => $this->popularAsserts->getContentRegexp(),
                        ]
                    ),
                    new Assert\Length(
                        [
                            'groups' => 'calendar_default',
                            'max' => 250,
                        ]
                    ),
                ],
            ]
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
                'validation_groups' => 'calendar_default',
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
        return 'calendar_type';
    }
}
