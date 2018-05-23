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

        return $query->select('c.id', 'c.title', 'c.description')->from('calendar', 'c');
    }

    /**
     * Finds one record by id from calendar table
     * @param int $id
     *
     * @return array|mixed
     */
    public function findOneById($id)
    {
        $result = $this->queryAll()
            ->where('c.id = :id')
            ->setParameter(':id', $id, \PDO::PARAM_STR)
            ->execute()->fetchAll();

        return !$result ? [] : current($result);
    }

    /**
     * Safes data to calendar table
     *
     * @param array $calendar
     *
     * @return int
     */
    public function save($calendar)
    {
        if (isset($calendar['id']) && ctype_digit((string) $calendar['id'])) {
            $id = $calendar['id'];
            unset($calendar['id']);

            return $this->db->update('calendar', $calendar, ['id' => $id]);
        }

        return $this->db->insert('calendar', $calendar);
    }
}
