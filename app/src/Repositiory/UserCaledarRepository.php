<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 20.05.18
 * Time: 18:59
 */

namespace Repositiory;

use DataManager\Search\SearchDataManager;
use DataManager\UserCalendarDataManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Query\QueryBuilder;
use Utils\MyPaginatorShort;

/**
 * Class UserCaledarRepository
 */
class UserCaledarRepository extends AbstractRepository
{
    /**
     *
     * @var CalendarRepository|null
     */
    private $calendarRepository = null;

    /**
     * CalendarRepository constructor.
     *
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        parent::__construct($db);
        $this->calendarRepository = new CalendarRepository($db);
    }

    /**
     * Gets paginated users and roles for calendar
     * form modified witch searchData query
     *
     * @param array $queryParams
     * @param null  $searchData
     *
     * @return null|\Pagerfanta\Pagerfanta
     */
    public function getSearchPaginatedUsersByCalendarId($queryParams, $searchData = null)
    {
        $query = $this->queryLinkedUserByCalendarId($queryParams['calendarId']);

        if (isset($searchData['user_role']) && !empty($searchData['user_role'])) {
            $query->andWhere('uC.user_role like :role')
                ->setParameter(':role', $searchData['user_role'], \PDO::PARAM_STR);
        }
        if (isset($searchData['email']) && !empty($searchData['email'])) {
            $query->andWhere('u.email like :email')
                ->setParameter(':email', $searchData['email'].'%', \PDO::PARAM_STR);
        }

        $paginator = new MyPaginatorShort(
            $query,
            '5',
            'uC.id',
            $queryParams['page']
        );

        return $paginator->pagerfanta;
    }

    /**
     * Gets paginated users and roles for calendar
     * form modified witch searchData query
     *
     * @param array $queryParams
     * @param null  $searchData
     *
     * @return null|\Pagerfanta\Pagerfanta
     */
    public function getSearchPaginatedCalendarsByUserId($queryParams, $searchData = null)
    {
        $query = $this->getLinkedCalendarsByUserId($queryParams['userId']);
        $searchDataManager = new SearchDataManager($query, 'c');
        $searchDataManager->addFilters($searchData);
        $paginator = new MyPaginatorShort(
            $query,
            '5',
            'uC.id',
            $queryParams['page']
        );

        return $paginator->pagerfanta;
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
            ->from('user_calendars', 'uC');
    }

    /**
     * Saves saves to user_calendar and calendar tables
     * @param array $calendar
     *
     * @param int   $userId
     *
     * @throws DBALException
     */
    public function save(array $calendar, $userId)
    {
        $this->db->beginTransaction();

        try {
            $this->calendarRepository->save($calendar);
            $calendar['calendar_id'] = $this->db->lastInsertId();
            $userCalendarManager = new UserCalendarDataManager($calendar, $userId);
            $userCalendarManager->setAdmin();
            $this->db->insert('user_calendars', $userCalendarManager->getUserCalendarData());
            $this->db->commit();
        } catch (DBALException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Find one record in user_calendars table by id
     *
     * @param int $userCalendarId
     *
     * @return array|mixed
     */
    public function findOneById($userCalendarId)
    {
        $qb = $this->queryAll()->where('id = :id')
            ->setParameter(':id', $userCalendarId, \PDO::PARAM_INT);
        $result = $qb->execute()->fetch();

        return $result ? $result : [];
    }

    /**
     * Update user role in user_calendars table witch id = $usercalendarId
     *
     * @param int   $userClendarId
     *
     * @param array $data
     *
     * @return mixed
     */
    public function updateUserRoleFoundById($userClendarId, array $data)
    {
        return $this->db->update('user_calendars', $data, ['id' => $userClendarId]);
    }

    /**
     * Delete one record from user_calendars
     *
     * @param int $userCalendarId
     */
    public function deleteLink($userCalendarId)
    {
        $this->db->delete('user_calendars', ['id' => $userCalendarId]);
    }

    /**
     * Link user to calendar
     *
     * @param int $userId
     * @param int $userRole
     * @param int $calendarId
     */
    public function linkUserToCalendar($userId, $userRole, $calendarId)
    {
        if ($calendarId && ctype_digit((string) $calendarId)) {
            $this->db->insert(
                'user_calendars',
                [
                    'user_id' => $userId,
                    'user_role' => $userRole,
                    'calendar_id' => $calendarId,
                ]
            );
        }
    }

    /**
     * Check wheather user and calendar are linked, if so true else false
     *
     * @param int $userId
     * @param int $calendarId
     *
     * @return bool
     */
    public function isLinked($userId, $calendarId)
    {
        $qb = $this->queryAll()->where('user_id = :userId')
            ->andWhere('calendar_id = :calendarId')
            ->setParameter(':userId', $userId, \PDO::PARAM_STR)
            ->setParameter(':calendarId', $calendarId, \PDO::PARAM_STR);
        $result = $qb->execute()->fetchAll();

        return $result && count($result);
    }

    /**
     * Delete calendar and all users
     * @param array $calendar
     *
     * @throws DBALException
     */
    public function delete($calendar)
    {
        $this->db->beginTransaction();
        try {
            $this->db->delete('user_calendars', ['calendar_id' => $calendar['id']]);
            $this->calendarRepository->deleteFoundById($calendar['id']);
            $this->db->commit();
        } catch (DBALException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    /**
     * Query one user and it's links to calendar by calendarId
     *
     * @param int $calendarId
     *
     * @return QueryBuilder $qb
     */
    public function queryLinkedUserByCalendarId($calendarId)
    {
        $qb = $this->db->createQueryBuilder();
        $qb = $qb->select('u.email', 'u.first_name', 'u.last_name', 'uC.user_role', 'uC.id')
            ->from('user_calendars', 'uC')
            ->innerJoin('uC', 'user', 'u', 'uC.user_id = u.id')
            ->where('uC.calendar_id = :calendarId')
            ->setParameter(':calendarId', $calendarId, \PDO::PARAM_INT);

        return $qb;
    }

    /**
     * Find user and its links by users_calendar table id
     *
     * @param int $userCalendarId
     *
     * @return array
     */
    public function findLinkedUserById($userCalendarId)
    {
        $qb = $this->db->createQueryBuilder();
        $qb = $qb->select('u.email', 'u.first_name', 'u.last_name', 'uC.user_role', 'uC.id')
            ->from('user_calendars', 'uC')
            ->innerJoin('uC', 'user', 'u', 'uC.user_id = u.id')
            ->where('uC.id = :id')
            ->setParameter(':id', $userCalendarId, \PDO::PARAM_INT);
        $result = $qb->execute()->fetch();

        return $result ? $result : [];
    }

    /**
     * Returns query for join user_calendar and calendar data
     *
     * @param int $userId
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function getLinkedCalendarsByUserId($userId)
    {
        $qb = $this->db->createQueryBuilder();
        $qb = $qb->select('uC.calendar_id', 'c.title', 'c.description')
            ->from('user_calendars', 'uC')
            ->innerJoin('uC', 'calendar', 'c', 'uC.calendar_id = c.id')
            ->where('uC.user_id = :userId')
            ->setParameter(':userId', $userId, \PDO::PARAM_STR);

        return $qb;
    }
}
