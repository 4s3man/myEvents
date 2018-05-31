<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 31.05.18
 * Time: 15:56
 */

namespace Calendar;

/**
 * Class Day
 */
class Day
{
    /**
     * @var \DateTime|null
     */
    protected $date = null;

    /**
     * @var null
     */
    protected $events = null;

    /**
     * Day constructor.
     *
     * @param \DateTime $date
     * @param array     $events
     */
    public function __construct(\DateTime $date, $events)
    {
        if (count($events) && (!($events[0] instanceof Event) || !($events[0] instanceof RecurrentEvent))) {
            throw new \InvalidArgumentException('Array of events needs to be Calendar\Event Type');
        }
        $this->date = $date;
        $this->events = $events;
    }
}
