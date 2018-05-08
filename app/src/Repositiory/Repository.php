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

abstract class Repository
{
    /**
     * @var \Doctrine\DBAL\Connection $db
     */
    protected $db;

    /**
     * userRepository constructor.
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
    abstract function queryAll();

    public function isUnique(String $value, $valueId, $id = null ){
        return $this->queryAll()->execute()->fetchAll();
    }

}