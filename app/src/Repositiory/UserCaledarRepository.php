<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 20.05.18
 * Time: 18:59
 */

namespace Repositiory;

use DataManager\UserCalendarDataManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;

/**
 * Class UserCaledarRepository
 */
class UserCaledarRepository extends AbstractRepository
{
    /**
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
     *
     * @param array $calendar
     * @param int   $userId
     *
     * @throws DBALException
     * @throws \Doctrine\DBAL\ConnectionException
     */
    public function save(array $calendar, int $userId)
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
     * Delete calendar and all users
     *
     * @param array $calendar
     *
     * @throws DBALException
     * @throws \Doctrine\DBAL\ConnectionException
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
     * Returns query for join user_calendar and calendar data
     *
     * @param int $userId
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function userCalendarJoinQuery(int $userId)
    {
        //TODO jak to rozwiązać?
        //Searcher don't work witch leftJoin :(
        $qb = $this->db->createQueryBuilder();
        $qb = $qb->select('uC.calendar_id', 'c.title', 'c.description')
            ->from('user_calendars', 'uC')
            ->innerJoin('uC', 'calendar', 'c', 'uC.calendar_id = c.id')
            ->where('uC.user_id = :userId')
            ->setParameter(':userId', $userId, \PDO::PARAM_STR);

        return $qb;
    }
}
