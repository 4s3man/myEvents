<?php

namespace Calendar;

use Plummer\Calendarful\Event\RecurrentEventInterface;

class RecurrentEvent extends Event implements RecurrentEventInterface
{
    protected $recurrenceType;

    protected $recurrenceUntil;

    public function __construct($rawEvent, $parentId = null, $occurrenceDate = null)
    {
        parent::__construct($rawEvent, $parentId, $occurrenceDate);
        $this->recurrenceType = $rawEvent['type'] ? $rawEvent['type'] : null;
        $this->recurrenceUntil = $rawEvent['until'] ? new \DateTime($rawEvent['until']) : null;
    }

    public function getRecurrenceType()
    {
        return $this->recurrenceType;
    }

    public function setRecurrenceType($type = null)
    {
        if ($type === null) {
            $this->recurrenceUntil = null;
        }

        $this->recurrenceType = $type;
    }

    public function getRecurrenceUntil()
    {
        return $this->recurrenceUntil;
    }
}
