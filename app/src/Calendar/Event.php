<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 31.05.18
 * Time: 15:56
 */

namespace Calendar;

use Plummer\Calendarful\Event\EventInterface;

/**
 * Class Event
 */
class Event implements EventInterface
{
    /**
     * @var id|null
     */
    protected $id = null;

    /**
     * @var \DateTime|null
     */
    protected $startDate = null;

    /**
     * @var \DateTime|null
     */
    protected $endDate = null;

    /**
     * @var int|null
     */
    protected $parentId = null;

    /**
     * @var \DateTime|null
     */
    protected $occurrenceDate = null;

    /**
     * @var array|null
     */
    protected $data = null;

    /**
     * Event constructor.
     *
     * @param array $rawEvent
     *
     * @param null  $parentId
     * @param null  $occurrenceDate
     */
    public function __construct(array $rawEvent, $parentId = null, $occurrenceDate = null)
    {
        $this->id = $rawEvent['id'];
        unset($rawEvent['id']);
        $this->startDate = new \DateTime($rawEvent['start']);
        unset($rawEvent['start']);
        $this->endDate = new \DateTime($rawEvent['end']);
        unset($rawEvent['end']);
        $this->parentId = $parentId;
        $this->occurrenceDate = $occurrenceDate ? new \DateTime($occurrenceDate) : null;
        $this->data = $rawEvent;
    }

    /**
     * @return id|mixed|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return \DateTime|null
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     * @param \DateTime $startDate
     */
    public function setStartDate(\DateTime $startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return \DateTime|null
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $endDate
     */
    public function setEndDate(\DateTime $endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return bool|\DateInterval
     */
    public function getDuration()
    {
        return $this->startDate->diff($this->endDate);
    }

    /**
     * @return int|mixed|null
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @return \DateTime|null
     */
    public function getOccurrenceDate()
    {
        return $this->occurrenceDate;
    }
}
