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
     * Find all values where $column = $value andWhere $value != $itself
     *
     * @param String $value  to be find for uniqueness
     * @param String $column name to be searched
     * @param String $itself to check if it's not it
     *
     * @return array
     */
    public function findForNotItselfUniqueness($value, $column, $itself);
}
