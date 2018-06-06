<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 29.05.18
 * Time: 01:05
 */

namespace Calendar;

use Plummer\Calendarful\Event\EventRegistryInterface;

/**
 * Class AdapterCalendarDataManagerCalendarfulCalendar
 */
class AdapterCalendarDataManagerCalendarfulCalendar implements EventRegistryInterface
{
    /**
     * @var array|null
     */
    protected $events = null;

    /**
     * @var array|null
     */
    protected $recurentEvents = null;

    /**
     * AdapterCalendarDataManagerCalendarfulCalendar constructor.
     *
     * @param array $events          elements instance of Event
     * @param array $reccurentEvents elements instance of recurrentEvent
     */
    public function __construct($events, $reccurentEvents = null)
    {
        $this->events = $events;
        //        $this->recurentEvents = $reccurentEvents;
    }

    /**
     * @param array $filters
     *
     * @return array|null|\Plummer\Calendarful\Event\EventInterface[]
     */
    public function getEvents(array $filters = array())
    {
        return $this->events;
    }

    /**
     * @param array $filters
     *
     * @return array|null|\Plummer\Calendarful\Event\EventInterface[]
     */
    public function getRecurrentEvents(array $filters = array())
    {
        return $this->recurentEvents;
    }
}
