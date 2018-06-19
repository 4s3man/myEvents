<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 22.05.18
 * Time: 01:27
 */

namespace DataManager;

/**
 * Class UserCalendarDataManager converts data and merges data
 */
class UserCalendarDataManager
{
    /**
     *
     * @var mixed|null
     */
    private $calendar = null;

    /**
     *
     * @var mixed|null
     */
    private $user = null;

    /**
     *
     * @var Array|null
     */
    private $userCalendarData = null;

    /**
     * UserCalendarDataManager constructor.
     *
     * @param array $calendar
     * @param array $userId
     */
    public function __construct($calendar, $userId)
    {
        $this->calendar = $calendar;
        $this->user = $userId;
        $this->setUserCalendarData();
    }

    /**
     * Sets admin as user role in userCalendarData variable
     */
    public function setAdmin()
    {
        $this->userCalendarData['user_role'] = 'calendar_admin';
    }

    /**
     *
     * @return Array|null $this->userCalendarData
     */
    public function getUserCalendarData()
    {
        return $this->userCalendarData;
    }

    /**
     * Creates proper user calendar data
     */
    private function setUserCalendarData()
    {
        $this->userCalendarData['user_id'] = $this->user;
        $this->userCalendarData['calendar_id'] = $this->calendar['calendar_id'];
    }
}
