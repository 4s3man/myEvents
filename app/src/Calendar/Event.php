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
     *
     * @var id|null
     */
    protected $id = null;

    /**
     *
     * @var \DateTime|null
     */
    protected $startDate = null;

    /**
     *
     * @var \DateTime|null
     */
    protected $endDate = null;

    /**
     *
     * @var int|null
     */
    protected $parentId = null;

    /**
     *
     * @var \DateTime|null
     */
    protected $occurrenceDate = null;

    /**
     *
     * @var array|null
     */
    protected $data = null;

    /**
     * Event constructor.
     *
     * @param array $rawEvent
     */
    public function __construct(array $rawEvent)
    {
        $this->id = isset($rawEvent['id']) ? $rawEvent['id'] : null;
        unset($rawEvent['id']);
        $this->startDate = isset($rawEvent['start']) ? new \DateTime($rawEvent['start']) : null;
        unset($rawEvent['start']);
        $this->endDate = isset($rawEvent['end']) ? new \DateTime($rawEvent['end']) : null;
        unset($rawEvent['end']);
        $this->parentId = isset($rawEvent['parentId']) ? $rawEvent['parentId'] : null;
        unset($rawEvent['parentId']);
        $this->occurrenceDate = isset($rawEvent['occurrenceDate']) ? new \DateTime($rawEvent['occurrenceDate']) : null;
        unset($rawEvent['occurrenceDate']);
        $this->data = $rawEvent;
    }

    /**
     *
     * @return id|mixed|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     *
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     *
     * @return \DateTime|null
     */
    public function getStartDate()
    {
        return $this->startDate;
    }

    /**
     *
     * @param \DateTime $startDate
     */
    public function setStartDate(\DateTime $startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     *
     * @return \DateTime|null
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     *
     * @param \DateTime $endDate
     */
    public function setEndDate(\DateTime $endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     *
     * @return bool|\DateInterval
     */
    public function getDuration()
    {
        return $this->startDate->diff($this->endDate);
    }

    /**
     *
     * @return int|mixed|null
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     *
     * @return \DateTime|null
     */
    public function getOccurrenceDate()
    {
        return $this->occurrenceDate;
    }

    /**
     *
     * @return array|null
     */
    public function getData()
    {
        return $this->data;
    }
}
