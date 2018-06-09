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
}
