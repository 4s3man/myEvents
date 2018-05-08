<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 08.05.18
 * Time: 23:53
 */

namespace Validator\Constraints;

use Repositiory\Repository;
use Symfony\Component\Validator\Constraint;

class Uniqueness extends Constraint
{
    /**
     * Message
     *
     * @var string
     */
    public $message = "value {{value}} is already in database";

    /**
     * Repository
     *
     * @var null|Repository
     */
    public $repository = null;

}