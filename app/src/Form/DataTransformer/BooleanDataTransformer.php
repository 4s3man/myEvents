<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 13.06.18
 * Time: 12:36
 */

namespace Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class BooleanDataTransformer
 */
class BooleanDataTransformer implements DataTransformerInterface
{
    /**
     * Transform passed data to boolean
     * @param mixed $signUpInt
     *
     * @return bool|mixed
     */
    public function transform($signUpInt)
    {
        return (bool) $signUpInt;
    }

    /**
     * Transform gathered to int
     * @param mixed $signUpBool
     *
     * @return int|mixed
     */
    public function reverseTransform($signUpBool)
    {
        return (int) $signUpBool;
    }
}
