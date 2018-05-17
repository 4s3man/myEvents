<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 16.05.18
 * Time: 12:13
 */

namespace Repositiory;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Static functions used by repositories
 *
 * Class StaticRepositoryFunctions
 */
class StaticRepositoryFunctions
{
    /**
     * Finds next free id of a table
     *
     * @param Connection $db
     *
     * @param String     $tableName
     *
     * @return int       $result
     */
    public static function getNextId(Connection $db, $tableName)
    {
        $qb = $db->createQueryBuilder();

        $qb = $qb->select('MIN(c1.id + 1)')->from($tableName, 'c1')
            ->leftJoin('c1', $tableName, 'c2', 'c1.id + 1 = c2.id')
            ->where($qb->expr()->isNull('c2.id'));
        $result = $qb->execute()->fetchColumn();

        return !$result || $result < 1 ? 1 : $result;
    }

    /**
     * Function used by every findForUniquenessable Repository
     *
     * @param QueryBuilder $allQueried
     *
     * @param String       $value
     * @param String       $column
     *
     * @return array
     */
    public static function findForUniqueness(QueryBuilder $allQueried, $value, $column)
    {

        $allQueried->where($column.' = :value')
            ->setParameter(':value', $value);

        return $allQueried->execute()->fetchAll();
    }
}
