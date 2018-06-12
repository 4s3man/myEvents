<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 11.06.18
 * Time: 16:54
 */
namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * Class MainImgType
 */
class MainImgType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
            'multiple' => false,
            'expanded' => true,
            'media' => null,
            )
        );
    }

    /**
     * @param FormView      $view
     *
     * @param FormInterface $form
     *
     * @param array         $options
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);
        $view->vars = array_merge(
            $view->vars,
            [
                'media' => $options['media'],
            ]
        );
    }

    /**
     * @return null|string
     */
    public function getBlockPrefix()
    {
        return 'mainImg';
    }

    /**
     * @return null|string
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}
