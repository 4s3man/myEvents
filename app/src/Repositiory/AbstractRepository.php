<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 06.06.18
 * Time: 19:00
 */

namespace Repositiory;

/**
 * Class AbstractRepository
 */
abstract class AbstractRepository
{
    protected $db = null;

    /**
     * AbstractRepository constructor.
     *
     * @param null $db
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    public function findMediaWitchIdIn($ids){
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        return $this->queryAll()->where('m.id IN (:ids)')
            ->setParameter(':ids', $ids, \Doctrine\DBAL\Connection::PARAM_INT_ARRAY);
    }
}
