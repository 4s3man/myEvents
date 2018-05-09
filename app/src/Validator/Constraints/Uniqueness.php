<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 08.05.18
 * Time: 23:53
 */

namespace Validator\Constraints;

use Repositiory\AbstractRepository;
use Repositiory\Repository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\MissingOptionsException;

/**
 * Class Uniqueness
 * Constraint to use in Uniqueness Validation
 */
class Uniqueness extends Constraint
{
    /**
     * Message
     *
     * @var string
     */
    public $message = "register.value_already_registered";

    /**
     * Repository
     *
     * @var null|AbstractRepository
     */
    public $repository = null;

    /**
     * Column name witch values has to be unique
     *
     * @var null|String
     */
    public $uniqueColumn = null;

    /**
     * Uniqueness constructor.
     * @param null $options
     */
    public function __construct($options = null)
    {
        if (null !== $options && !is_array($options)) {
            $options = array(
                'repository' => $options,
                'uniqueColumn' => $options,
            );
        }

        parent::__construct($options);

        if (null === $this->repository || null === $this->uniqueColumn) {
            throw new MissingOptionsException(sprintf('Options uniqueColumn and repository are obligatory for constraint %s', __CLASS__), array('repository', 'uniqueColumn'));
        }
    }
}
