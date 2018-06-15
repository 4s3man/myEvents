<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 08.06.18
 * Time: 16:31
 */

namespace Validator\Constraints\Interfaces;

/**
 * Interface UniquenessInterface
 * needs to be implemented for custom Uniqueness Validator
 */
interface NotItselfUniquenessInterface
{
    /**
     * Find all values where $column = $value
     *
     * @param String $value  to be find for uniqueness
     * @param String $column name to be searched
     *
     * @return array
     */
    public function findForNotItselfUniqueness($value, $column, $itself);
}
