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
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class SearchType
 */
class UserSearchType extends AbstractType
{
    /**
     *
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
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'email',
            TextType::class,
            [
                'label' => 'search',
                'required' => false,
                'constraints' => $this->popularAsserts->slugOptional(['search_default']),
            ]
        );
        $builder->add(
            'user_role',
            ChoiceType::class,
            [
                'label' => 'label.user_role',
                'required' => false,
                'choices' => [
                    'All' => '',
                    'ADMIN' => 'ADMIN',
                    'EDITOR' => 'EDITOR',
                ],
                'constraints' => [
                    new Assert\Choice(
                        [
                            'choices' => [
                                '',
                                'ADMIN',
                                'EDITOR',
                            ],
                        ]
                    ),
                ],
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
