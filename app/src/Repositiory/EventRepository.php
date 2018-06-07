<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 16.05.18
 * Time: 00:25
 */

namespace Repositiory;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Plummer\Calendarful\Event\EventInterface;
use Plummer\Calendarful\Event\EventRegistryInterface;

/**
 * Class CalendarRepository
 */
class EventRepository extends AbstractRepository implements EventRegistryInterface
{
    /**
     * @var Connection|null Database to use
     */
    protected $db = null;

    protected $tagRepository = null;

    protected $calendarId = null;

    /**
     * CalendarRepository constructor.
     *
     * @param Connection $db
     */
    public function __construct(Connection $db, $calendarId = null)
    {
        parent::__construct($db);
        $this->tagRepository = new TagRepository($db);
        $this->calendarId = $calendarId;
    }

    /**
     * Query all from calendar
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function queryAll()
    {
        $qb = $this->db->createQueryBuilder();
        $qb->select(
            'e.id',
            'e.title',
            'e.content',
            'e.start',
            'e.end',
            'e.seats',
            'e.cost',
            'e.calendar_id'
        )->from('event', 'e');
        if (null !== $this->calendarId) {
            $qb->where('calendar_id = :calendarId')
                ->setParameter(':calendarId', $this->calendarId, \PDO::PARAM_INT);
        }

        return $qb;
    }

    /**
     * Get record by Id
     *
     * @param int $eventId
     *
     * @return mixed
     */
    public function getEventById($eventId)
    {
        $qb = $this->queryAll()->where('e.id = :eventId')
            ->setParameter(':eventId', $eventId, \PDO::PARAM_INT);

        return $qb->execute()->fetch();
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
        $this->db->beginTransaction();

        try {
            $tagsIds = isset($event['tags']) ? array_column($event['tags'], 'id') : [];
            unset($event['tags']);

            if (isset($event['id']) && ctype_digit((string) $event['id'])) {
                $id = $event['id'];
                unset($event['id']);
                $this->removeLinkedTags($id);
                $this->addLinkedTags($id, $tagsIds);
                $this->db->update('event', $event, ['id' => $id]);
            } else {
                $this->db->insert('event', $event);
                $lastEventId = $this->db->lastInsertId();
                $this->addLinkedTags($lastEventId, $tagsIds);
            }
            $this->db->commit();
        } catch (DBALException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Gets data used to create Calendar\Events objects
     * in specific time range passend in $filters array
     *
     * @param array $filters
     *
     * @return array|EventInterface[]
     */
    public function getEvents(array $filters = array())
    {
        $innerQb = $this->db->createQueryBuilder();
        $qb = $this->queryAll()
            ->where('DATEDIFF(start, :toDate) <=0')
            ->andWhere('DATEDIFF(end, :fromDate) >=0')
            ->setParameter(':toDate', $filters['toDate'], \PDO::PARAM_STR)
            ->setParameter(':fromDate', $filters['fromDate'], \PDO::PARAM_STR);

        $result = $qb->execute()->fetchAll();

        return isset($result) ? $result : [];
    }

    /**
     * Add event tags to event_tags table
     *
     * @param int   $eventId
     * @param mixed $tagIds
     */
    public function addLinkedTags(int $eventId, $tagIds)
    {
        if (!is_array($tagIds)) {
            $tagIds = [$tagIds];
        }

        foreach ($tagIds as $tagId) {
            $this->db->insert(
                'event_tags',
                [
                    'event_id' => $eventId,
                    'tags_id' => $tagId,
                ]
            );
        }
    }

    /**
     * Gets data used to create Calendar\Events objects
     * in specific time range passend in $filters array
     *
     * @param array $filters
     *
     * @return array|EventInterface[]
     */
    public function getRecurrentEvents(array $filters = array())
    {
        //        $innerQb = $this->db->createQueryBuilder();
        //        $qb = $this->queryAll()
        //            ->where('type != "non_recurrent"')
        //            ->andwhere('DATEDIFF(until, :fromDate) >=0 OR until IS NULL')
        //            ->setParameter(':fromDate', $filters['fromDate'], \PDO::PARAM_STR);
        //
        //        $result = $qb->execute()->fetchAll();
        //
        //        return $result;
    }

    /**
     * @param int $eventId
     *
     * @return int result
     *
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    protected function removeLinkedTags(int $eventId)
    {
        return $this->db->delete('event_tags', ['event_id' => $eventId]);
    }
}
