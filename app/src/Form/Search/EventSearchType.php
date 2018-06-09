<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 04.06.18
 * Time: 09:41
 */

namespace Form\Search;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

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
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);

        //        $builder->add(
        //            'type',
        //            ChoiceType::class,
        //            [
        //                'choices' => [
        //                  'all' => 'all',
        //                  'normal' => 'non_recurrent',
        //                  'recurrent' => 'recurrent',
        //                  'daily recurrent' => 'daily',
        //                   'weekly recurrent' => 'weekly',
        //                   'monthly recurrent' => 'monthly',
        //                ],
        //                'constraints' => [
        //                    new Assert\Choice(
        //                        [
        //                            'choices' => [
        //                                'non_recurrent',
        //                                'recurrent',
        //                                'daily',
        //                                'weekly',
        //                                'monthly',
        //                            ],
        //                        ]
        //                    ),
        //                ],
        //            ]
        //        );
    }

    /**
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
     * @return null|string
     */
    public function getBlockPrefix()
    {
        return 'search_type';
    }
}
