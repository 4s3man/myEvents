<?php /**
       *
       * @noinspection PhpCSValidationInspection 
       */

/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 28.05.18
 * Time: 17:55
 */

namespace DataManager;

use Calendar\AdapterCalendarDataManagerCalendarfulCalendar;
use Calendar\CalendarPage;
use Calendar\Day;
use Calendar\Event;
use Plummer\Calendarful\Calendar\Calendar;
use Plummer\Calendarful\Recurrence\RecurrenceFactory;
use Repositiory\EventRepository;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Yasumi\Yasumi;

/**
 * Class CalendarDataManager
 */
class CalendarDataManager
{
    /**
     *
     * @var null|string
     */
    protected $date = null;

    /**
     *
     * @var array|null
     */
    protected $events = null;

    /**
     *
     * @var array|null
     */
    protected $recurrentEvents = null;

    /**
     *
     * @var null
     */
    protected $daysInMonth = null;

    /**
     *
     * @var array|null
     */
    protected $range = null;

    /**
     *
     * @var null|Calendar
     */
    protected $eventsList = null;

    /**
     *
     * @var null|\Yasumi\Provider\AbstractProvider
     */
    protected $holidays = null;

    /**
     *
     * @var null
     */
    protected $eventRepository = null;

    /**
     * CalendarDataManager constructor.
     * @param EventRepository $eventRepository
     *
     * @param null            $date
     *
     * @throws \ReflectionException
     */
    public function __construct(EventRepository $eventRepository, $date = null)
    {
            $this->date = new \DateTime($date);

            $this->daysInMonth = cal_days_in_month(
                CAL_GREGORIAN,
                $this->date->format('m'),
                $this->date->format('Y')
            );

            $this->range = $this->setRange();

            try {
                $this->holidays = Yasumi::create('Poland', $this->date->format('Y'));
            } catch (\ReflectionException $e) {
                throw $e;
            }

            $eventsRaw = $eventRepository
                ->getEvents($this->formatDateArray($this->range, 'Y-m-d'));
            $this->events = $this->makeEventsFromRawData($eventsRaw, Event::class);

            $this->eventsList = $this->makeEventsList(
                $this->range['fromDate'],
                $this->range['toDate']
            );
    }

    /**
     * Makes eventList, instance of Plummer\Calendarful\Calendar\Calendar;
     *
     * @param \DateTime $fromDate
     * @param \DateTime $toDate
     *
     * @return Calendar
     */
    public function makeEventsList(\DateTime $fromDate, \DateTime $toDate)
    {
        $adapter = new AdapterCalendarDataManagerCalendarfulCalendar($this->events);
        $calendar = new Calendar($this->makeRecurrenceFactory());
        $calendar->populate($adapter, $fromDate, $toDate);

        return $calendar;
    }

    /**
     * Makes array filled witch Calendar\Days objects
     *
     * @return CalendarPage
     *
     * @throws \Exception
     */
    public function makeCalendarMonthPage()
    {
        if (null === $this->eventsList) {
            throw new MissingMandatoryParametersException(
                'makeCalendarMonthPage() needs eventsList specified, first call makeEventsList'
            );
        }

        $days = $this->makeDays();
        $calendarPage = new CalendarPage($days, $this->range['fromDate']);

        return $calendarPage;
    }

    public function getNextMonth()
    {
        $date = clone $this->date;
        return $date->add(\DateInterval::createFromDateString('+1 month'));
    }

    public function getPrevMonth()
    {
        $date = clone $this->date;
        return $date->add(\DateInterval::createFromDateString('-1 month'));
    }

    /**
     * Make Calendar\Days including holidays, events, and dayDate
     *
     * @return array
     */
    private function makeDays()
    {
        $days = [];
        $date = clone $this->range['fromDate'];
        for ($i = 1; $i <= $this->daysInMonth; $i++) {
            $date = new \DateTime($date->format('Y-m-').(string) $i.' 23:59:59');
            $events = $this->getEventsForDate($date);
            $holidays = $this->getHolidaysForDate($date);
            $day = new Day($date, $events, $holidays);
            $days[] = $day;
        }

        return $days;
    }

    /**
     * Gets all events for specific date
     *
     * @param \DateTime $date
     *
     * @return array
     */
    private function getEventsForDate(\DateTime $date)
    {
        $events = [];
        foreach ($this->eventsList as $event) {
            if ($this->dateInEventRange($event, $date)) {
                $events[] = $event;
            }
        }

        return $events;
    }


    /**
     * Gets holidays provided by Yasumi for specyfic days
     *
     * @param \DateTime $date
     *
     * @return array eventName => \DateTime date
     */
    private function getHolidaysForDate(\DateTime $date)
    {
        $holidays = [];
        $dayString = $date->format('Y-m-d');
        foreach ($this->holidays as $name => $holidayDate) {
            $holiday = $holidayDate->format('Y-m-d');
            if ($holiday === $dayString) {
                $holidays[$name] = $holidayDate;
            }
        }

        return $holidays;
    }

    /**
     *
     * @param Event     $event
     * @param \DateTime $date
     *
     * @return bool
     */
    private function dateInEventRange(Event $event, \DateTime $date)
    {
        $dateStart = new \DateTime($date->format('Y-m-d').' 00:00:00');
        $dateEnd = new \DateTime($date->format('Y-m-d').' 23:59:59');

        return $dateEnd >= $event->getStartDate() && $dateStart <= $event->getEndDate();
    }

    /**
     * Makes Range specified by date passed in construct
     *
     * @return array
     */
    private function setRange()
    {
        $cloned4start = clone $this->date;
        $cloned4end = clone $this->date;
        $range = [
            'fromDate' => $cloned4start->modify('first day of this month'),
            'toDate' => $cloned4end->modify('last day of this month'),
        ];

        return $range;
    }

    /**
     * Converts array of \DateTime objects to array of stringified in specific
     * format date
     *
     * @param array  $filters
     * @param string $format
     *
     * @return array
     */
    private function formatDateArray(array $filters, $format)
    {
        if (!(current($filters) instanceof \DateTime)) {
            throw new \InvalidArgumentException('All filters values must have instance of \DateTime.');
        }

        $formatted = [];
        foreach ($filters as $key => $filter) {
            $formatted[$key] = $filter->format($format);
        }

        return $formatted;
    }

    /**
     * Converts array of strings to array of Events
     * for calendarful package
     *
     * @param array  $rawEvents
     *
     * @param string $class
     *
     * @return array
     */
    private function makeEventsFromRawData(array $rawEvents, $class)
    {
        $events = [];
        foreach ($rawEvents as $rawEevent) {
            $event = new $class($rawEevent);
            $events[$rawEevent['id']] = $event;
        }

        return $events;
    }

    /**
     *
     * @return RecurrenceFactory
     */
    private function makeRecurrenceFactory()
    {
        $recurrenceFactory = new RecurrenceFactory();
        $recurrenceFactory->addRecurrenceType('daily', 'Plummer\Calendarful\Recurrence\Type\Daily');
        $recurrenceFactory->addRecurrenceType('weekly', 'Plummer\Calendarful\Recurrence\Type\Weekly');
        $recurrenceFactory->addRecurrenceType('monthly', 'Plummer\Calendarful\Recurrence\Type\MonthlyDate');

        return $recurrenceFactory;
    }
}
