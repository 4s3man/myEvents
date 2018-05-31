<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 16.05.18
 * Time: 00:25
 */

namespace Repositiory;

use Doctrine\DBAL\Connection;
use Plummer\Calendarful\Event\EventInterface;
use Plummer\Calendarful\Event\EventRegistryInterface;

/**
 * Class CalendarRepository
 */
class EventRepository implements  EventRegistryInterface
{
    /**
     * @var Connection|null Database to use
     */
    private $db = null;

    /**
     * CalendarRepository constructor.
     *
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

        return $query->select(
            'e.id',
            'e.title',
            'e.content',
            'e.start',
            'e.end',
            'e.seats',
            'e.cost',
            'e.calendar_id',
            'e.until',
            'e.type'
        )->from('event', 'e');
    }

    /**
     * Saves data to event table
     *
     * @param array $event
     *
     * @return int
     */
    public function save($event)
    {
        if (isset($event['id']) && ctype_digit((string) $event['id'])) {
            //TODO event update
        }

        return $this->db->insert('event', $event);
    }

    public function getEvents(array $filters = array())
    {
        $innerQb = $this->db->createQueryBuilder();
        $qb = $this->queryAll()
            ->where($innerQb->expr()->isNull('type'))
            ->andwhere('DATEDIFF(start, :toDate) <=0')
            ->andWhere('DATEDIFF(end, :fromDate) >=0')
            ->setParameter(':toDate', $filters['toDate'], \PDO::PARAM_STR)
            ->setParameter(':fromDate', $filters['fromDate'], \PDO::PARAM_STR);
        $result = $qb->execute()->fetchAll();

        return $result;
    }

    public function getRecurrentEvents(array $filters = array())
    {
        $innerQb = $this->db->createQueryBuilder();
        $qb = $this->queryAll()
            ->where($innerQb->expr()->isNotNull('type'))
            ->andwhere('DATEDIFF(until, :fromDate) >=0 OR until IS NULL')
            ->setParameter(':fromDate', $filters['fromDate'], \PDO::PARAM_STR);

        $result = $qb->execute()->fetchAll();

        return $result;
    }
}
