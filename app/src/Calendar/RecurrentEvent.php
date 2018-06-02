<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 31.05.18
 * Time: 15:56
 */
namespace Calendar;

use Plummer\Calendarful\Event\RecurrentEventInterface;

/**
 * Class RecurrentEvent
 */
class RecurrentEvent extends Event implements RecurrentEventInterface
{
    /**
     * @var null
     */
    protected $recurrenceType = null;

    /**
     * @var \DateTime|null
     */
    protected $recurrenceUntil = null;

    /**
     * RecurrentEvent constructor.
     * @param array $rawEvent
     */
    public function __construct(array $rawEvent)
    {
        $this->recurrenceType = $rawEvent['type'] ? $rawEvent['type'] : null;
        unset($rawEvent['type']);
        $this->recurrenceUntil = $rawEvent['until'] ? new \DateTime($rawEvent['until']) : null;
        unset($rawEvent['until']);
        parent::__construct($rawEvent);
    }

    /**
     * @return mixed|null
     */
    public function getRecurrenceType()
    {
        return $this->recurrenceType;
    }

    /**
     * @param null $type
     */
    public function setRecurrenceType($type = null)
    {
        if (null === $type) {
            $this->recurrenceUntil = null;
        }

        $this->recurrenceType = $type;
    }

    /**
     * @return \DateTime|null
     */
    public function getRecurrenceUntil()
    {
        return $this->recurrenceUntil;
    }
}
