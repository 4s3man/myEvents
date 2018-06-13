<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 16.05.18
 * Time: 00:25
 */

namespace Repositiory;

use DataManager\EventDataManager;
use DataManager\Search\SearchDataManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Plummer\Calendarful\Event\EventInterface;
use Plummer\Calendarful\Event\EventRegistryInterface;
use Utils\MyPaginatorShort;

/**
 * Class EventRepository
 */
class EventRepository extends AbstractRepository implements EventRegistryInterface
{
    /**
     *
     * @var Connection|null Database to use
     */
    protected $db = null;

    /**
     *
     * @var null|TagRepository
     */
    private $tagRepository = null;

    /**
     *
     * @var int|null
     */
    private $calendarId = null;

    /**
     * EventRepository constructor.
     *
     * @param Connection $db
     *
     * @param int|null   $calendarId
     */
    public function __construct(Connection $db, $calendarId = null)
    {
        parent::__construct($db);
        $this->tagRepository = new TagRepository($db);
        $this->calendarId = $calendarId;
    }

    /**
     * Gets paginated results form modified witch searchData query
     *
     * @param array $queryParams
     * @param null  $searchData
     *
     * @return null|\Pagerfanta\Pagerfanta
     */
    public function getSearchedAndPaginatedRecords($queryParams, $searchData = null)
    {
        //todo nowe query z obrazkami
        $qb = $this->db->createQueryBuilder();
        $qb->select('e.id','e.calendar_id','e.title','e.start','e.end','e.seats','e.cost','e.content', 'e.main_img','m.photo')
            ->from('event','e')
            ->leftJoin('e','media','m', 'e.main_img = m.id')
            ->where('e.calendar_id = :calendarId')
            ->setParameter(':calendarId', $queryParams['calendarId'], \PDO::PARAM_INT);

        $searchDataManager = new SearchDataManager($qb, 'e');
        $searchDataManager->addFilters($searchData);
        $paginator = new MyPaginatorShort(
            $qb,
            '5',
            'e.id',
            $queryParams['page']
        );


        return $paginator->pagerfanta;
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
            'e.calendar_id',
            'e.sign_up',
            'e.main_img'
        )->from('event', 'e');

        return $qb;
    }

    /**
     * Query all events with specific calendarId
     *
     * @param int $calendarId
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function queryAllForCalendarId($calendarId)
    {
        $qb = $this->queryAll()->where('calendar_id = :calendarId')
            ->setParameter(':calendarId', $this->calendarId, \PDO::PARAM_INT);

        return $qb;
    }

    /**
     * Get record by Id
     *
     * @param int $eventId
     *
     * @return mixed
     */
    public function findOneById($eventId)
    {
        $qb = $this->queryAll()->where('e.id = :eventId')
            ->setParameter(':eventId', $eventId, \PDO::PARAM_INT);

        return $qb->execute()->fetch();
    }

    /**
     * Updates Event where id matches
     *
     * @param array $event
     */
    public function updateEvent(array $event)
    {
        if (isset($event['id']) && ctype_digit((string) $event['id'])) {
            $id = $event['id'];
            unset($event['id']);

            $this->db->update('event', $event, ['id' => $id]);
        }
    }

    /**
     * Saves data to event table
     *
     * @param array $eventRaw
     * @param int   $calendarId
     *
     * @throws DBALException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function save($eventRaw, $calendarId)
    {
        $eventDataManager = new EventDataManager($eventRaw, $calendarId);
        $event = $eventDataManager->makeEventForSave();
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
        $qb = $this->queryAllForCalendarId($this->calendarId)
            ->andwhere('DATEDIFF(start, :toDate) <=0')
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
     *
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

    /**
     * Converts string date start, end to \DateTimeObject
     * @param array $data
     *
     * @return array
     */
    private function dateToDatetimeObject(array $data)
    {
        $results = $this->columnToDataTime($data, 'start');
        $results = $this->columnToDataTime($data, 'end');

        return $results;
    }

    /**
     * Convert column of arrays to \DateTime Object
     *
     * @param array  $array
     *
     * @param string $column
     *
     * @return array
     */
    private function columnToDataTime(array $array, $column)
    {
        $result = $array;
        $arrayColumn = array_column($array, $column);

        foreach ($arrayColumn as $key => $value) {
            $arrayColumn[$key] = new \DateTime($value);
        }

        foreach ($result as $key => &$val) {
            $val[$column] = $arrayColumn[$key];
        }

        return $result;
    }
}
