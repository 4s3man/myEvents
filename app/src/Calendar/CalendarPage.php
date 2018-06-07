<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 02.06.18
 * Time: 14:24
 */

namespace Calendar;

/**
 * Class CalendarPage
 */
class CalendarPage
{
    /**
     * @var array
     */
    protected $days = [];

    /**
     * @var null|string
     */
    protected $year = null;

    /**
     * @var mixed|null
     */
    protected $monthName = null;

    /**
     * @var null|string
     */
    protected $daysWeekNum = null;

    /**
     * @var array
     */
    protected $monthNames = [
        'month_name.jan',
        'month_name.fab',
        'month_name.mar',
        'month_name.apr',
        'month_name.may',
        'month_name.jun',
        'month_name.jul',
        'month_name.aug',
        'month_name.sep',
        'month_name.oct',
        'month_name.nov',
        'month_name.dec',
        ];

    /**
     * @var array
     */
    protected $weekNames = [
        'week_name.mon',
        'week_name.tus',
        'week_name.wed',
        'week_name.thu',
        'week_name.fri',
        'week_name.sat',
        'week_name.sun',
    ];

    /**
     * CalendarPage constructor.
     *
     * @param array     $days
     * @param \DateTime $firstDayOfMonth
     */
    public function __construct(array $days, \DateTime $firstDayOfMonth)
    {
        $this->days = $days;
        $this->year = $firstDayOfMonth->format('Y');
        $this->monthName = $this->monthNames[$firstDayOfMonth->format('n')-1];
        $this->daysWeekNum = $firstDayOfMonth->format('N');
    }

    /**
     * @return array
     */
    public function getDays()
    {
        return $this->days;
    }

    /**
     * @return null|string
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @return mixed|null
     */
    public function getMonthName()
    {
        return $this->monthName;
    }

    /**
     * @return null|string
     */
    public function getDaysWeekNum()
    {
        return $this->daysWeekNum;
    }

    /**
     * @return array
     */
    public function getMonthNames()
    {
        return $this->monthNames;
    }

    /**
     * @return array
     */
    public function getWeekNames()
    {
        return $this->weekNames;
    }
}
