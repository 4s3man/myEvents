<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 06.06.18
 * Time: 17:39
 */

namespace Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class TagDataTransformer
 */
class DateDataTransformer implements DataTransformerInterface
{
    /**
     * Function runed filling form witch data
     *
     * @param string $date
     *
     * @return mixed|string
     */
    public function transform($date)
    {
        $date = new \DateTime('now');

        return $date;
    }

    /**
     * Function runed at form submit
     *
     * @param mixed $date
     *
     * @return array|mixed
     */
    public function reverseTransform($date)
    {
        return $date;
    }
}
