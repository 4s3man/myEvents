<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 20.05.18
 * Time: 18:59
 */

namespace Repositiory;


class UserCaledarRepository
{
    /**
     * @var Connection|null Database to use
     */
    private $db = null;

    /**
     * CalendarRepository constructor.
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Prepare first query part
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function queryAll()
    {
        return $this->db->createQueryBuilder()
            ->select('uC.id', 'uC.user_id', 'uC.calendar_id', 'uC.user_role')
            ->from('user', 'u');
    }

    public function save($data)
    {

    }


}