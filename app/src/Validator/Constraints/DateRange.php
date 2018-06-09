<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 08.06.18
 * Time: 14:22
 */

namespace Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\MissingOptionsException;

/**
 *
 * @Annotation
 */
class DateRange extends Constraint
{
    public $minMessage = 'This date should be greater than {{ limit }}.';
    public $maxMessage = 'This date should be less than {{ limit }}.';
    public $invalidMessage = 'This value should be a valid date.';
    public $min;
    public $max;

    /**
     * DateRange constructor.
     *
     * @param null $options
     */
    public function __construct($options = null)
    {
        parent::__construct($options);

        if (null === $this->min && null === $this->max) {
            throw new MissingOptionsException(sprintf('Either option "min" or "max" must be given for constraint %s', __CLASS__), array('min', 'max'));
        }

        if (null !== $this->min) {
            $this->min = new \DateTime($this->min);
        }

        if (null !== $this->max) {
            $this->max = new \DateTime($this->max);
        }
    }
}
