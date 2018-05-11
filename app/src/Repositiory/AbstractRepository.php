<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 09.05.18
 * Time: 00:30
 */

namespace Repositiory;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Class AbstractRepository
 */
abstract class AbstractRepository
{
    /**
     * @var \Doctrine\DBAL\Connection $db
     */
    protected $db;

    /**
     * userRepository constructor.
     *
     * @param \Doctrine\DBAL\Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Query all values
     *
     * @return QueryBuilder;
     */
    abstract public function queryAll();

    /**
     * Function used by every findForUniquenessable Repository
     *
     * @param String $value  to be find for uniqueness
     * @param String $column name witch $value in it
     *
     * @return array
     */
    public function findForUniqueness($value, $column)
    {

        return $this->queryAll()
            ->where($column.' = :value')
            ->setParameter(':value', $value)
            ->execute()->fetchAll();
    }
}
