<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 25.04.18
 * Time: 23:07
 */

namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class TagType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            TextType::class,
            [
                'label'=>'label.name',
                'required'=>true,
                'attr' => [
                    'max_length' => 128,
                    'value'=>'gimeThatSheet',
                    'class'=>'dafuk',
                ],
                'constraints'=>[
                new Assert\Length(
                    [
                          'max' => 4,
                      ]
                ),
                ],
            ]
        );
        $builder->add(
            'submit',
            SubmitType::class,
            [
                'label'=>'label.submit',
            ]
        );
    }

    public function getBlockPrefix()
    {
        return 'tag_type';
    }
}
