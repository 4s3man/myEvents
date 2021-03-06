<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 04.06.18
 * Time: 09:41
 */

namespace Form\Search;

use Form\Helpers\Regexps;
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
     * @var Regexps|null
     */
    protected $popularAsserts = null;

    /**
     * SearchType constructor.
     */
    public function __construct()
    {
        $this->popularAsserts = new Regexps();
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
                'label' => 'label.search_email',
                'required' => false,
                'constraints' => [
                    new Assert\Regex(
                        [
                            'groups' => ['search_default'],
                            'pattern' => $this->popularAsserts->getSlugRegexp(),
                        ]
                    ),
                    new Assert\Length(
                        [
                            'groups' => ['search_default'],
                            'max' => 45,
                        ]
                    ),
                ],
            ]
        );
        $builder->add(
            'user_role',
            ChoiceType::class,
            [
                'label' => 'label.user_role',
                'required' => false,
                'choices' => [
                    'label.all' => '',
                    'label.calendar_admin' => 'calendar_admin',
                    'label.calendar_editor' => 'calendar_editor',
                ],
                'constraints' => [
                    new Assert\Choice(
                        [
                            'choices' => [
                                '',
                                'calendar_admin',
                                'calendar_editor',
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
