<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 08.05.18
 * Time: 23:53
 */

namespace Validator\Constraints;

use Repositiory\AbstractRepository;

use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\MissingOptionsException;
use Symfony\Component\Validator\Exception\InvalidOptionsException;
use Validator\Constraints\Interfaces\UniquenessInterface;

/**
 * Class Uniqueness
 * Constraint to use in Uniqueness Validation
 */
class PasswordMatch extends Constraint
{
    /**
     * Message
     *
     * @var string
     */
    public $message = 'validator.password_not_match';

    /**
     * Repository
     *
     * @var null|AbstractRepository
     */
    public $password = null;

    /**
     * Column name witch values has to be unique
     *
     * @var null|String
     */
    public $bcrypt = null;

    /**
     * Uniqueness constructor.
     *
     * @param null $options
     */
    public function __construct($options = null)
    {
        if (null !== $options && !is_array($options)) {
            $options = array(
                'password' => $options,
                'bcrypt' => $options,
            );
        }

        parent::__construct($options);

        if (null === $this->password || null === $this->bcrypt) {
            throw new MissingOptionsException(sprintf('Options password and bcrypt are obligatory for constraint %s', __CLASS__), array('bcrypt', 'password'));
        }
        if (!($this->bcrypt instanceof BCryptPasswordEncoder)) {
            throw new InvalidOptionsException(sprintf('Bcrypt must be instance of %s', BCryptPasswordEncoder::class), array('bcrypt'));
        }
    }
}
