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
     * @var int|null
     */
    private $calendarId = null;

    /**
     * @var array|null
     */
    private $event = null;

    /**
     * EventDataManager constructor.
     *
     * @param array $event
     * @param int   $calendarId
     */
    public function __construct(array $event, $calendarId)
    {
        $this->event = $event;
        $this->calendarId = $calendarId;
    }

    /**
     * Format data from form for save to db
     *
     * @return array|null
     */
    public function makeEventForSave()
    {
        $formData = $this->event;

        if (isset($formData['sign_up'])) {
            $formData['sign_up'] = (int) $formData['sign_up'];
        }
        $formData['calendar_id'] = $this->calendarId;
        $formData['sign_up'] = isset($formData['sign_up']) ? (int) $formData['sign_up'] : 0;
        $formData['start'] = isset($formData['start']) ? $formData['start']->format('Y-m-d H:i:s') : null;
        $formData['end'] = isset($formData['end']) ? $formData['end']->format('Y-m-d H:i:s') : null;

        return $formData;
    }

    /**
     * @return null
     */
    public function makeEvent()
    {
        $this->event['start'] = new \DateTime($this->event['start']);
        $this->event['end'] = new \DateTime($this->event['end']);

        return $this->event;
    }

    /**
     * Gets signUp if needs to render it
     *
     * @return bool|mixed
     */
    public function getSignUp()
    {
        $signUp = isset($this->event['sign_up']) ? $this->event['sign_up'] : false;

        return $signUp;
    }

    /**
     * Check if any seets/tickets for event remain
     *
     * @return bool
     */
    public function seatsRemain()
    {
        return isset($this->event['seats']) && $this->event['seats'] > 0;
    }

    /**
     * @return array|null
     */
    public function getEvent()
    {
        return $this->event;
    }
}
