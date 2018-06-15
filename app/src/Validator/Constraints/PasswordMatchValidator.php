<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 09.05.18
 * Time: 00:10
 */

namespace Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Class UniquenessValidator
 */
class PasswordMatchValidator extends ConstraintValidator
{
    /**
     *
     * @param mixed      $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint->bcrypt || !$constraint->password) {
            return;
        }

        if (!$constraint->bcrypt->isPasswordValid($constraint->password, $value, '')) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{value}}', $value)
                ->addViolation();
        }
    }
}
