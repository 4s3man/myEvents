<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 28.05.18
 * Time: 17:55
 */

namespace DataManager;

class CalendarDataManager
{
    protected $events;

    protected $firstDayOfMonth;

    protected $daysInMonth;

    protected $calendar;

    public function __construct($events, $date)
    {
        $yearMonth = explode('-',$date);
        $this->events = $events;
        $this->firstDayOfMonth = date('Y-m-01', strtotime($date));
        $this->daysInMonth = cal_days_in_month(CAL_GREGORIAN, $yearMonth[1],$yearMonth[0]);
        $this->createCalendar();
    }

    public function createCalendar()
    {
        $startDays = $this->getDayFromEvent('start_date');

        dump($startDays);

        for ($day=1; $day < $this->daysInMonth; $day++) {

        }
    }

    private function getDayFromEvent($startStop)
    {
        $days = [];
        $dates = array_column($this->events, $startStop);
        foreach ($dates as $date) {
            $days[] = explode('-',$date)[2];
        }

        return $days;
    }
}