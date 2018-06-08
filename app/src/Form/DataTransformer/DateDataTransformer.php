<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 06.06.18
 * Time: 17:39
 */

namespace Form\DataTransformer;

use Repositiory\TagRepository;
use Symfony\Component\Form\DataTransformerInterface;

/**
 * Class TagDataTransformer
 */
class DateDataTransformer implements DataTransformerInterface
{
    /**
     * Function runed filling form witch data
     *
     * @param mixed $tags
     *
     * @return mixed|string
     */
    public function transform($date)
    {
        $date = new \DateTime('now');
        return $date;
    }

    /**
     * Function runed at form submmit
     *
     * @param mixed $string
     *
     * @return array|mixed
     */
    public function reverseTransform($date)
    {
        return $date;
    }
}
