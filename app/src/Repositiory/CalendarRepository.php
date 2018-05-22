<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 16.05.18
 * Time: 00:25
 */

namespace Repositiory;

use Doctrine\DBAL\Connection;

/**
 * Class CalendarRepository
 */
class CalendarRepository
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
     * Query all from calendar
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function queryAll()
    {
        $query = $this->db->createQueryBuilder();

        return $query->select('c.id', 'c.token')->from('calendar', 'c');
    }

    /**
     * Saves data to calendar table
     *
     * @param Array $calendar
     */
    public function save($calendar)
    {
        $this->db->insert('calendar', $calendar);
    }
}
