<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 28.05.18
 * Time: 17:55
 */

namespace DataManager;

use Calendar\AdapterCalendarDataManagerCalendarfulCalendar;
use Calendar\Day;
use Calendar\Event;
use Calendar\RecurrentEvent;
use Plummer\Calendarful\Calendar\Calendar;
use Plummer\Calendarful\Recurrence\RecurrenceFactory;
use Repositiory\EventRepository;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;

/**
 * Class CalendarDataManager
 */
class CalendarDataManager
{
    /**
     * @var null|string
     */
    protected $date = null;

    /**
     * @var array|null
     */
    protected $events = null;

    /**
     * @var array|null
     */
    protected $recurrentEvents = null;

    /**
     * @var null
     */
    protected $daysInMonth = null;

    /**
     * @var array|null
     */
    protected $range = null;

    /**
     * @var null|Calendar
     */
    protected $eventsList = null;

    /**
     * CalendarDataManager constructor.
     *
     * @param EventRepository $eventRepository
     * @param string          $date
     */
    public function __construct(EventRepository $eventRepository, $date)
    {
        $this->date = $date;
        $this->range = $this->setRange();

        $eventsRaw = $eventRepository->getEvents($this->range);
        $this->events = $this->makeEvents($eventsRaw);

        $recurrentEventsRaw = $eventRepository->getRecurrentEvents($this->range);
        $this->recurrentEvents = $this->makeRecurrentEvents($recurrentEventsRaw);

        $this->eventsList = $this->makeEventsList(
            new \DateTime($this->range['fromDate']),
            new \DateTime($this->range['toDate'])
        );

//        dump($this->eventsList);

        $calendarPage = $this->makeCalendarMonthPage();
        //TODO potestować czy wszystkie rekurencyjne dodają się dobrze
        dump($calendarPage);
    }

    /**
     * Makes eventList, instance of Plummer\Calendarful\Calendar\Calendar;
     *
     * @param \DateTime $fromDate
     * @param \DateTime $endDate
     *
     * @return Calendar
     */
    public function makeEventsList(\DateTime $fromDate, \DateTime$endDate)
    {
        $adapter = new AdapterCalendarDataManagerCalendarfulCalendar($this->events, $this->recurrentEvents);
        $calendar = new Calendar($this->makeRecurrenceFactory());
        $calendar->populate($adapter, new \DateTime($this->range['fromDate']), new \DateTime($this->range['toDate']));

        return $calendar;
    }

    /**
     * Makes array filled witch Calendar\Days objects
     *
     * @return array
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

        $calendar = [];
        for ($i = 1; $i < $this->daysInMonth; $i++) {
            $date = new \DateTime($this->date.'-'.(string) $i);
            $events = [];
            foreach ($this->eventsList->getIterator() as $event) {
                if ($this->dateInEventRange($event, $date)) {
                    $events[] = $event;
                }
            }
            $day = new Day($date, $events);
            $calendar[] = $day;
        }

        return $calendar;
        //TODO rozdzielić eventy do dni, pseudokod
        //dla liczb od 1 do $this->>daysInMonth
        //zrób new \DateTime dla $this->>date . liczba z powyżej
        //dla wszystkich eventów w liście sprawdź czy data dnia pomiędzy startDate i endDate
        //jeśli tak to dodaj te eventy do tablicy eventów dnia, dzień jako osobny obiekt posiadający
        //liste eventów, klasę do obiektu html, swoją datę
        //kalendarz jako lista dni
    }

    private function dateInEventRange($event, \DateTime $date)
    {
        return '' === $this->dateDifference($event->getStartDate(), $date)
            && '-' === $this->dateDifference($event->getEndDate(), $date);
    }

    private function dateDifference($date_1 , $date_2 , $differenceFormat = '%r' )
    {
        $interval = date_diff($date_1, $date_2);

        return $interval->format($differenceFormat);
    }

    private function setRange()
    {
        $yearMonth = explode('-', $this->date);
        $this->daysInMonth = cal_days_in_month(CAL_GREGORIAN, $yearMonth[1], $yearMonth[0]);
        $range = [
            'fromDate' => $this->date.'-01',
            'toDate' => $this->date.'-'.$this->daysInMonth,
        ];

        return $range;
    }

    private function makeEvents($rawEvents)
    {
        $events = [];
        foreach ($rawEvents as $rawEevent)
        {
            $event = new Event($rawEevent);
            $events[$rawEevent['id']] = $event;
        }

        return $events;
    }

    private function makeRecurrentEvents($rawEvents)
    {
        $events = [];
        foreach ($rawEvents as $rawEevent)
        {
            $event = new RecurrentEvent($rawEevent);
            $events[$rawEevent['id']] = $event;
        }

        return $events;
    }

    private function makeRecurrenceFactory()
    {
        $recurrenceFactory = new RecurrenceFactory();
        $recurrenceFactory->addRecurrenceType('daily', 'Plummer\Calendarful\Recurrence\Type\Daily');
        $recurrenceFactory->addRecurrenceType('weekly', 'Plummer\Calendarful\Recurrence\Type\Weekly');
        $recurrenceFactory->addRecurrenceType('monthly', 'Plummer\Calendarful\Recurrence\Type\MonthlyDate');

        return $recurrenceFactory;
    }
}
