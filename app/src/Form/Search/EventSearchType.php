<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 04.06.18
 * Time: 09:41
 */

namespace Form\Search;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SearchType
 */
class EventSearchType extends SearchType
{
    /**
     * SearchType constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
        $builder->add(
            'start',
            TextType::class,
            [
                'label' => 'label.searchYearMonth',
                'required' => false,
                'constraints' => [
                    new Assert\Regex(
                        [
                          'groups' => ['search_default'],
                          'pattern' => '/[1-3]{1}[0-9]{3}-(0[1-9]|1[0-2])/',
                        ]
                    ),
                ],
            ]
        );
    }

    /**
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            [
                'validation_groups' => ['search_default'],
            ]
        );
    }

    /**
     *
     * @return null|string
     */
    public function getBlockPrefix()
    {
        return 'search_type';
    }
}
