<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 26.05.18
 * Time: 00:53
 */

namespace DataManager;

use Repositiory\EventRepository;

/**
 * Class EventDataManager
 */
class EventDataManager
{

    protected $event = null;

    protected $eventRepository = null;

    protected $allowedKeys = [
        'signUp',
        'calendar_id',
        'id',
        'title',
        'content',
        'cost',
        'seats',
        'start',
        'end',
        'media',
        'tags',
        ];

    protected $signUp = null;

    /**
     * EventDataManager constructor.
     *
     * @param array $formData
     * @param int   $calendarId
     */
    public function __construct(array $formData, $calendarId = null)
    {
        if (count(array_diff(array_keys($formData), $this->allowedKeys))) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Invalid array keys for class %s 1st construct argument, allowed values are %s',
                    __CLASS__,
                    implode('","', $this->allowedKeys)
                )
            );
        }

        if (isset($formData['signUp']) && true === $formData['signUp']) {
            //TODO make signUp
        }
        unset($formData['signUp']);

        $this->event = $formData;
        $this->event['calendar_id'] = isset($this->event['calendar_id']) ? : $calendarId;
    }

    /**
     * @return null
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @return null
     */
    public function getSignUp()
    {
        return $this->signUp;
    }
}
