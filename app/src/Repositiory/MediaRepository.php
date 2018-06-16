<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 16.05.18
 * Time: 00:25
 */

namespace Repositiory;

use DataManager\Search\SearchDataManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Utils\MyPaginatorShort;

/**
 * Class CalendarRepository
 */
class MediaRepository extends AbstractRepository
{
    /**
     *
     * @var Connection|null
     */
    protected $db = null;

    protected $tagsRepository = null;

    /**
     * MediaRepository constructor.
     *
     * @param Connection $db
     *
     * @param null       $userId
     *
     * @param null       $calendarId
     */
    public function __construct(Connection $db, $userId = null, $calendarId = null)
    {
        parent::__construct($db);
        $this->tagsRepository = new TagRepository($db);
    }

    /**
     * Gets paginated results form modified witch searchData query
     *
     * @param array $queryParams
     * @param null  $searchData
     *
     * @return null|\Pagerfanta\Pagerfanta
     */
    public function getSearchedAndPaginatedRecordsForUserAndCalendar($queryParams, $searchData = null)
    {
        $query = $this->queryCalendarAndUserMedia($queryParams['calendarId'], $queryParams['userId']);
        $searchDataManager = new SearchDataManager($query, $searchData);
        $paginator = new MyPaginatorShort(
            $query,
            '5',
            'm.id',
            $queryParams['page']
        );

        return $paginator->pagerfanta;
    }

    /**
     * Find all records for specified user and calendar
     * @param int $userId
     * @param int $calendarId
     *
     * @return array
     */
    public function findAllForUserAndCalendar($userId, $calendarId)
    {
        $qb = $this->queryCalendarAndUserMedia($calendarId, $userId);
        $result = $qb->execute()->fetchAll();

        return $result;
    }

    public function findOneById($id)
    {
        $qb = $this->queryAll()->where('id = :id')
            ->setParameter(':id', $id, \PDO::PARAM_INT);
        $result = $qb->execute()->fetch();

        return $result ? $result : [];
    }

    /**
     * Gets paginated results form modified witch searchData query
     *
     * @param array $queryParams
     * @param null  $searchData
     *
     * @return null|\Pagerfanta\Pagerfanta
     */
    public function getSearchedAndPaginatedRecordsForUser($queryParams, $searchData = null)
    {
        $query = $this->queryUserMedia($queryParams['userId']);
        $searchDataManager = new SearchDataManager($query, 'm');
        $searchDataManager->addFilters($searchData);
        $paginator = new MyPaginatorShort(
            $query,
            '5',
            'm.id',
            $queryParams['page']
        );

        return $paginator->pagerfanta;
    }

    /**
     * Gets paginated results form modified witch searchData query
     *
     * @param array $queryParams
     * @param null  $searchData
     *
     * @return null|\Pagerfanta\Pagerfanta
     */
    public function getSearchedAndPaginatedRecordsForCalendar($queryParams, $searchData = null)
    {
        //todo skończyć to
        $query = $this->queryCalendarMedia($queryParams['calendarId']);
        $searchDataManager = new SearchDataManager($query, 'm');
        $searchDataManager->addFilters($searchData);
        $paginator = new MyPaginatorShort(
            $query,
            '2',
            'm.id',
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
        $qb->select('m.id', 'm.title', 'm.photo')->from('media', 'm');

        return $qb;
    }

    /**
     *
     * @param array    $photo
     *
     * @param int      $userId
     * @param int|null $calnedarId
     *
     * @throws DBALException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function saveToUser($photo, $userId)
    {
        $this->db->beginTransaction();
        try {
            if (isset($photo['id']) && ctype_digit((string) $photo['id'])) {
                $id = $photo['id'];
                unset($photo['id']);

                $this->db->update('media', $photo, ['id' => $id]);
            } else {
                $this->db->insert('media', $photo);
                $mediaId = $this->db->lastInsertId();
                $this->linkMediaToUser($userId, $mediaId);
            }
            $this->db->commit();
        } catch (DBALException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function deleteUserMediaLink($userId, $mediaId)
    {
        $qb = $this->db->createQueryBuilder();
        $qb = $qb->delete('user_media')
            ->where('user_id = :userId')
            ->andWhere('media_id = :mediaId')
            ->setParameter(':userId', $userId, \PDO::PARAM_INT)
            ->setParameter(':mediaId', $mediaId, \PDO::PARAM_INT);

        return $qb->execute();
    }

    public function deleteCalendarMediaLink($calendarId, $mediaId)
    {
        $qb = $this->db->createQueryBuilder();
        $qb = $qb->delete('calendar_media')
            ->where('calendar_id = :calendarId')
            ->andWhere('media_id = :mediaId')
            ->setParameter(':calendarId', $calendarId, \PDO::PARAM_INT)
            ->setParameter(':mediaId', $mediaId, \PDO::PARAM_INT);

        return $qb->execute();
    }

    /**
     *
     * @param array    $photo
     *
     * @param int      $userId
     * @param int|null $calnedarId
     *
     * @throws DBALException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function saveToCalendar($photo, $calnedarId)
    {
        $this->db->beginTransaction();
        try {
            if (isset($photo['id']) && ctype_digit((string) $photo['id'])) {
                $id = $photo['id'];
                unset($photo['id']);

                $this->db->update('media', $photo, ['id' => $id]);
            } else {
                $this->db->insert('media', $photo);
                $mediaId = $this->db->lastInsertId();
                $this->linkMediaToCalendar($calnedarId, $mediaId);
            }
            $this->db->commit();
        } catch (DBALException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Links tables media and user via id in table user_media
     *
     * @param int $userId
     *
     * @param int $mediaId
     */
    public function linkMediaToUser($userId, $mediaId)
    {
        $this->db->insert(
            'user_media',
            [
               'user_id' => $userId,
               'media_id' => $mediaId,
            ]
        );
    }

    /**
     * Link media to calendar in calendar_media table
     * @param int $calendarId
     * @param int $mediaId
     */
    public function linkMediaToCalendar($calendarId, $mediaId)
    {
        $this->db->insert(
            'calendar_media',
            [
                'calendar_id' => $calendarId,
                'media_id' => $mediaId,
            ]
        );
    }

    /**
     * Query media linked to specified calendar and user
     * @param int $calendarId
     * @param int $userId
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function queryCalendarAndUserMedia($calendarId, $userId)
    {
        $qb = $this->db->createQueryBuilder();
        $qb->select('m.id', 'm.photo', 'm.title')->from('media', 'm')
            ->leftJoin('m', 'calendar_media', 'cM', 'cM.media_id = m.id')
            ->leftJoin('m', 'user_media', 'uM', 'uM.media_id = m.id')
            ->where('cM.calendar_id = :calendarId')
            ->orWhere('uM.user_id = :userId')
            ->setParameter(':calendarId', $calendarId, \PDO::PARAM_INT)
            ->setParameter(':userId', $userId, \PDO::PARAM_INT);

        return $qb;
    }

    /**
     * Query user and media linked to him
     * @param int $userId
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function queryUserMedia($userId)
    {
        $qb = $this->db->createQueryBuilder();
        $qb->select('uM.user_id', 'm.id', 'm.photo', 'm.title')->from('media', 'm')
            ->join('m', 'user_media', 'uM', 'uM.media_id = m.id')
            ->where('uM.user_id = :userId')
            ->setParameter(':userId', $userId, \PDO::PARAM_INT);

        return $qb;
    }

    /**
     * Query calendar_id and media liked to it
     * @param int $calendarId
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    private function queryCalendarMedia($calendarId)
    {
        $qb = $this->db->createQueryBuilder();
        $qb->select('cM.calendar_id', 'm.id', 'm.photo', 'm.title')->from('media', 'm')
            ->join('m', 'calendar_media', 'cM', 'cM.media_id = m.id')
            ->where('cM.calendar_id= :calendarId')
            ->setParameter(':calendarId', $calendarId, \PDO::PARAM_INT);

        return $qb;
    }
}
