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
    protected $calendarId = null;

    protected $event = null;

    /**
     * EventDataManager constructor.
     *
     * @param array $formData
     * @param int   $calendarId
     */
    public function __construct(array $event, $calendarId = null)
    {
        $this->event = $event;
        $this->calendarId = $calendarId;
    }

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
     * @return null
     */
    public function createSignUp($formFactory)
    {
        return 1;
    }

    public function getSignUp()
    {
        $signUp = isset($this->event['sign_up']) ? $this->event['sign_up'] : false;

        return $signUp;
    }

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
