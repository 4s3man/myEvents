<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 08.05.18
 * Time: 23:53
 */

namespace Validator\Constraints;

use Repositiory\AbstractRepository;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\MissingOptionsException;
use Symfony\Component\Validator\Exception\InvalidOptionsException;
use Validator\Constraints\Interfaces\NotItselfUniquenessInterface;
use Validator\Constraints\Interfaces\UniquenessInterface;

/**
 * Class Uniqueness
 * Constraint to use in Uniqueness Validation
 */
class NotItselfUniqueness extends Constraint
{
    /**
     * Message
     *
     * @var string
     */
    public $message = "editUser.value_in_use";

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
     * @var null | String
     */
    public $itself = null;

    /**
     * Uniqueness constructor.
     *
     * @param null $options
     */
    public function __construct($options = null)
    {
        if (null !== $options && !is_array($options)) {
            $options = array(
                'repository' => $options,
                'uniqueColumn' => $options,
                'itself' => $options,
            );
        }

        parent::__construct($options);

        if (null === $this->repository || null === $this->uniqueColumn || null === $this->itself) {
            throw new MissingOptionsException(sprintf('Options uniqueColumn and repository are obligatory for constraint %s', __CLASS__), array('repository', 'uniqueColumn'));
        }
        if (!($this->repository instanceof NotItselfUniquenessInterface)) {
            throw new InvalidOptionsException(sprintf('Passed repository must implements %s', NotItselfUniquenessInterface::class), array('repository'));
        }
    }
}
