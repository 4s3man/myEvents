<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 08.05.18
 * Time: 20:33
 */

namespace Form;

use Form\Helpers\Regexps;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;
use Validator\Constraints as CustomAsssert;

/**
 * Class SignUpType
 *
 * Used by creatBuilder funcition in controller
 */
class SignUpType extends AbstractType
{
    /**
     * Asserts helper
     *
     * @var Regexps|null
     */
    private $popularAsserts = null;

    /**
     * SignUpType constructor.
     */
    public function __construct()
    {
        $this->popularAsserts = new Regexps();
    }
    /**
     *
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'sign_up_type';
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
                'validation_groups' => 'sign_up_default',
                'repository' => null,
            ]
        );
    }

    /**
     *
     * @inheritdoc
     *
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->add(
            'first_name',
            TextType::class,
            [
                'label' => 'label.first_name',
                'required' => true,
                'attr' => [],
                'constraints' => [
                    new Assert\NotBlank(
                        [
                            'groups' => [ 'sign_up_default' ],
                        ]
                    ),
                    new Assert\Regex(
                        [
                            'groups' => [ 'sign_up_default' ],
                            'pattern' => $this->popularAsserts->getNameRegexp(),
                        ]
                    ),
                    new Assert\Length(
                        [
                            'groups' => [ 'sign_up_default' ],
                            'max' => 45,
                        ]
                    ),
                ],
            ]
        );
        $builder->add(
            'last_name',
            TextType::class,
            [
                'label' => 'label.last_name',
                'required' => true,
                'attr' => [],
                'constraints' => [
                    new Assert\NotBlank(
                        [
                            'groups' => [ 'sign_up_default' ],
                        ]
                    ),
                    new Assert\Regex(
                        [
                            'groups' => [ 'sign_up_default' ],
                            'pattern' => $this->popularAsserts->getNameRegexp(),
                        ]
                    ),
                    new Assert\Length(
                        [
                            'groups' => [ 'sign_up_default' ],
                            'max' => 45,
                        ]
                    ),
                ],
            ]
        );
        $builder->add(
            'email',
            EmailType::class,
            [
                'label' => 'label.email',
                'required' => true,
                'constraints' => [
                    new Assert\Email(
                        [
                            'groups' => [ 'sign_up_default' ],
                        ]
                    ),
                    new CustomAsssert\Uniqueness(
                        [
                        'groups' => [ 'sign_up_default' ],
                        'repository' => isset($options['repository']) ? $options['repository'] : null,
                        'uniqueColumn' => 'email',
                        ]
                    ),
                    new Assert\NotBlank(
                        [
                             'groups' => ['sign_up_default'],
                        ]
                    ),
                ],
            ]
        );
    }
}
