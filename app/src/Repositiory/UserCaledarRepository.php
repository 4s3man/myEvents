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
     * @param array $calendar to be saved in calendar table
     * @param int   $userId   to be used in
     */
    public function save($calendar, $userId)
    {
        $this->calendarRepository->save($calendar);
        $calendar['calendar_id'] = $this->db->lastInsertId();
        $userCalendarManager = new UserCalendarDataManager($calendar, $userId);
        $userCalendarManager->setAdmin();
        $this->db->insert('user_calendar', $userCalendarManager->getUserCalendarData());
    }
}
