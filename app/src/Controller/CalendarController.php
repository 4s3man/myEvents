<?php
/**
 * Created by PhpStorm.
 * User: Kuba Kułaga
 * Date: 29.04.18
 * Time: 10:38
 */

namespace Controller;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;

/**
 * Class CalendarController
 */
class CalendarController implements ControllerProviderInterface
{
    /**
     * CalendarController routing
     *
     * @param Application $app
     *
     * @return mixed | \Silex\ControllerCollection
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->get('/{calendarId}', [$this, 'calendarAction'])
            ->bind('home');
        $controller->get('/{calendarId}/addEvent', [$this, 'addAction'])
            ->bind('eventAdd');
        $controller->get('/{calendarId}/events/page/{page}', [$this, 'eventsIndexAction'])
            ->bind('eventsIndex');
        $controller->get('/{calendarId}/event/{eventId}', [$this, 'eventPageAction'])
            ->bind('eventShow');
        $controller->get('/{calendarId}/event/{eventId}/edit', [$this, 'editAction'])
            ->bind('eventEdit');

        return $controller;
    }

    /**
     * Action run by controller
     * Showing calendar
     *
     * @param Application $app
     *
     * @param String      $calendarId
     *
     * @return mixed
     */
    public function calendarAction(Application $app, $calendarId)
    {
        return $app['twig']->render(
            'calendar/calendar.html.twig',
            [
                'calendarId' => $calendarId,
            ]
        );
    }

    /**
     * Action run by controller
     * Adding event
     *
     * @param Application $app
     *
     * @param String      $calendarId
     *
     * @return mixed
     */
    public function addAction(Application $app, $calendarId)
    {
        return $app['twig']->render(
            'calendar/addEvent.html.twig',
            [
                'calendarId' => $calendarId,
            ]
        );
    }

    /**
     * Action for controller
     * Showing list of editable events
     *
     * @param Application $app
     *
     * @param int         $calendarId
     * @param int         $page
     *
     * @return mixed
     */
    public function eventsIndexAction(Application $app, $calendarId, $page)
    {
        return $app['twig']->render(
            'calendar/index.html.twig',
            [
                'calendarId' => $calendarId,
            ]
        );
    }

    /**
     * Action for controller
     * Showing single event
     *
     * @param Application $app
     *
     * @param String      $calendarId
     * @param String      $eventId
     *
     * @return mixed
     */
    public function eventPageAction(Application $app, $calendarId, $eventId)
    {
        return $app['twig']->render(
            'calendar/singleEvent.html.twig',
            [
                'calendarId' => $calendarId,
                'eventId' => $eventId,
            ]
        );
    }

    /**
     * Action for controller
     * Showing single event edit panel
     *
     * @param Application $app
     *
     * @param String      $calendarId
     * @param String      $eventId
     *
     * @return mixed
     */
    public function editEvent(Application $app, $calendarId, $eventId)
    {
        return $app['twig']->render(
            'calendar/editEvent.html.twig',
            [
                'calendarId' => $calendarId,
                'eventId' => $eventId,
            ]
        );
    }
}
