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
     * @var null|MediaRepository
     */
    private $mediaRepository = null;

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
        $this->mediaRepository = new MediaRepository($db);
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
        $qb = $this->db->createQueryBuilder();
        $qb->select('e.id', 'e.calendar_id', 'e.title', 'e.start', 'e.end', 'e.seats', 'e.cost', 'e.content', 'e.main_img', 'm.photo')
            ->from('event', 'e')
            ->leftJoin('e', 'media', 'm', 'e.main_img = m.id')
            ->where('e.calendar_id = :calendarId')
            ->setParameter(':calendarId', $queryParams['calendarId'], \PDO::PARAM_INT);

        if (isset($searchData['tags']) && count($searchData['tags'])) {
            $eventsIds = $this->getEventIdsWithTagsByTagIds($searchData['tags']);
            $qb->andWhere('e.id IN (:tags)')
                ->setParameter(':tags', $eventsIds, Connection::PARAM_INT_ARRAY);
            unset($searchData['tags']);
        }

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
     * Get array of ids of events witch tags specified by id
     * @param array $tagIds
     *
     * @return array
     */
    public function getEventIdsWithTagsByTagIds(array $tagIds)
    {
        $qb = $this->db->createQueryBuilder();
        $qb->select('eT.event_id')->from('event_tags', 'eT')
            ->where('tags_id IN (:tagIds)')
            ->setParameter(':tagIds', $tagIds, Connection::PARAM_INT_ARRAY);
        $result = $qb->execute()->fetchAll();

        return $result ? array_column($result, 'event_id') : [];
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
     * Query all events with this object calendar id
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function queryAllForCalendarId()
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
        $event = $qb->execute()->fetch();
        $event['tags'] = $this->getLinkedTagsById($eventId);
        $event['media'] = null !== $event['main_img'] ? $this->getMainImg($event['main_img']) : null;

        return $event;
    }

    /**
     * Get linked tags by eventId
     * @param int $eventId
     *
     * @return array
     */
    public function getLinkedTagsById($eventId)
    {
        $qb = $this->db->createQueryBuilder();
        $qb->select('t.name')->from('tags', 't')
            ->join('t', 'event_tags', 'eT', 't.id = eT.tags_id')
            ->where('eT.event_id = :eventId')
            ->setParameter(':eventId', $eventId, \PDO::PARAM_INT);
        $result = $qb->execute()->fetchAll();

        return $result ? array_column($result, 'name') : [];
    }

    /**
     * Get photo from db by event main_img id
     * @param int $mainImg
     *
     * @return array|mixed
     */
    public function getMainImg($mainImg)
    {
        $result = $this->mediaRepository->findOneById($mainImg);

        return $result;
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
            unset($event['media']);

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
     * Delete event and its links
     *
     * @param int $eventId
     *
     * @return int
     *
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function delete($eventId)
    {
        $this->removeLinkedTags($eventId);
        $this->removeLinkedParticipants($eventId);

        return $this->db->delete('event', ['id' => $eventId]);
    }

    /**
     * Delete participants linked to event
     *
     * @param int $eventId
     *
     * @return int
     *
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function removeLinkedParticipants($eventId)
    {
        return $this->db->delete('participant', ['event_id' => $eventId]);
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
        $qb = $this->queryAllForCalendarId()
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
    public function addLinkedTags($eventId, $tagIds)
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
     * Optional for recurrent events using Calendarfull
     *
     * @param array $filters
     *
     * @return array
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
        return [];
    }

    /**
     *
     * @param int $eventId
     *
     * @return mixed
     *
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    protected function removeLinkedTags($eventId)
    {
        return $this->db->delete('event_tags', ['event_id' => $eventId]);
    }
}
