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
            'title',
            TextType::class,
            [
                'label' => 'label.search_title',
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
