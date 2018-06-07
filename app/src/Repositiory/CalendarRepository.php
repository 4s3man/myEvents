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
class CalendarRepository extends AbstractRepository
{
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
     *
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

    /**
     * @param int $id of clendar to be deleted
     *
     * @return int
     *
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function deleteFoundById($id)
    {
        return $this->db->delete('calendar', ['id' => $id]);
    }
}
