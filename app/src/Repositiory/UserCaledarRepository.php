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
use Utils\MyPaginatorShort;

/**
 * Class UserCaledarRepository
 */
class UserCaledarRepository
{
    /**
     * @var Connection|null Database to use
     */
    private $db = null;

    /**
     * @var CalendarRepository|null
     */
    private $calendarRepository = null;

    /**
     * CalendarRepository constructor.
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
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
            ->from('user_calendar', 'uC');
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
    public function save($calendar, $userId)
    {
        $this->db->beginTransaction();

        try {
            $this->calendarRepository->save($calendar);
            $calendar['calendar_id'] = $this->db->lastInsertId();
            $userCalendarManager = new UserCalendarDataManager($calendar, $userId);
            $userCalendarManager->setAdmin();
            $this->db->insert('user_calendar', $userCalendarManager->getUserCalendarData());
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
            $this->db->delete('user_calendar', ['calendar_id' => $calendar['id']]);
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
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    public function userCalendarJoinQuery()
    {
        $qb = $this->db->createQueryBuilder();
        $qb->select('uC.id', 'uC.user_id', 'uC.calendar_id', 'uC.user_role', 'c.title', 'c.description')
            ->from('user_calendar', 'uC')->join('uC', 'calendar', 'c', 'uC.calendar_id = c.id');

        return $qb;
    }
}
