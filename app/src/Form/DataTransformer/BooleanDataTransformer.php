<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 13.06.18
 * Time: 12:36
 */

namespace Form\DataTransformer;


use Symfony\Component\Form\DataTransformerInterface;

class BooleanDataTransformer implements DataTransformerInterface
{

    public function transform($signUpInt)
    {
        return (bool) $signUpInt;
    }

    public function reverseTransform($signUpBool)
    {
        return (int) $signUpBool;
    }
}