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
class NotItselfUniquenessValidator extends ConstraintValidator
{
    /**
     *
     * @param mixed      $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint->repository || !$constraint->uniqueColumn || !$constraint->itself) {
            return;
        }
        $result = $constraint->repository->findForNotItselfUniqueness($value, $constraint->uniqueColumn, $constraint->itself);

        if ($result && count($result)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{value}}', $value)
                ->addViolation();
        }
    }
}
