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
        //TODO pytanie czy klasa Repository Asserts container z tablicami jak this->username assert nie lepsz od dziedziczenia?
        //TODO dodać ograniczenia co miesięczny event nie może trwać więcej niż miesiąc
        //TODO codzienny więcej niż dzień, coroczny więcej niż rok
        $builder->add(
            'title',
            TextType::class,
            [
                'label' => 'label.calendar_title',
                'required' => true,
                'constraints' => $this->popularAsserts->usernameAsserts('calendar_default'),
            ]
        );
        $builder->add(
            'description',
            TextType::class,
            [
                'label' => 'label.calendar_description',
                'required' => false,
                'constraints' => $this->popularAsserts->longTextAsserts(['calendar_default']),
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
