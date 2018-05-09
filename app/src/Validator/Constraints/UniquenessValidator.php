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
class UniquenessValidator extends ConstraintValidator
{
    /**
     * @param mixed      $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint->repository || !$constraint->uniqueColumn) {
            return;
        }
        $result = $constraint->repository->findForUniqueness($value, $constraint->uniqueColumn);

        if ($result && count($result)) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{value}}', $value)
                ->addViolation();
        }
    }
}
