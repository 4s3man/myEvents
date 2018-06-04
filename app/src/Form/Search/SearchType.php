<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 04.06.18
 * Time: 09:41
 */

namespace Form\Search;

use Form\Helpers\PopularAssertGroups;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SearchType
 */
class SearchType extends AbstractType
{
    /**
     * @var PopularAssertGroups|null
     */
    protected $popularAsserts = null;

    /**
     * SearchType constructor.
     */
    public function __construct()
    {
        $this->popularAsserts = new PopularAssertGroups();
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        //TODO wywalić error kiedy wartość jest pusta
        $builder->add(
            'title',
            TextType::class,
            [
                'label' => 'search',
                'required' => false,
                'constraints' => $this->popularAsserts->notObligatoryUsernameAsserts(['search_default']),
            ]
        );

        $builder->add(
            'type',
            ChoiceType::class,
            [
                'choices' => [
                  'all' => 'all',
                  'normal' => 'non_recurrent',
                  'recurrent' => 'recurrent',
                  'daily recurrent' => 'daily',
                   'weekly recurrent' => 'weekly',
                   'monthly recurrent' => 'monthly',
                ],
                'constraints' => [
                    new Assert\Choice(
                        [
                            'choices' => [
                                'non_recurrent',
                                'recurrent',
                                'daily',
                                'weekly',
                                'monthly',
                            ],
                        ]
                    ),
                ],
            ]
        );
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
