<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 26.05.18
 * Time: 00:53
 */

namespace DataManager;

/**
 * Class EventDataManager
 */
class EventDataManager
{
    /**
     * @var null
     */
    protected $event = null;

    /**
     * EventDataManager constructor.
     *
     * @param int $event
     * @param int $calendarId
     */
    public function __construct($event, $calendarId)
    {
        $event['calendar_id'] = $calendarId;
        $this->event = $event;
    }

    /**
     * @return null
     */
    public function getEvent()
    {
        return $this->event;
    }
}
