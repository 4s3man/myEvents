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
        $controller->get('/{userId}', [$this, 'showCalendar']);
        $controller->get('/{userId}/add', [$this, 'addEvent']);
        $controller->get('/{userId}/showAll', [$this, 'showAllEvents']);
        $controller->get('/{userId}/event/{eventId}', [$this, 'showEvent']);
        $controller->get('/{userId}/event/{eventId}/edit', [$this, 'editEvent']);

        return $controller;
    }

    /**
     * Action run by controller
     * Showing calendar
     *
     * @param Application $app
     *
     * @param String      $userId
     *
     * @return mixed
     */
    public function showCalendar(Application $app, $userId)
    {
        return $app['twig']->render(
            'calendar/calendar.html.twig',
            [
                'userId' => $userId,
            ]
        );
    }

    /**
     * Action run by controller
     * Adding event
     *
     * @param Application $app
     *
     * @param String      $userId
     *
     * @return mixed
     */
    public function addEvent(Application $app, $userId)
    {
        return $app['twig']->render(
            'calendar/addEvent.html.twig',
            [
                'userId' => $userId,
            ]
        );
    }

    /**
     * Action for controller
     * Showing list of editable events
     *
     * @param Application $app
     *
     * @param String      $userId
     *
     * @return mixed
     */
    public function showAllEvents(Application $app, $userId)
    {
        return $app['twig']->render(
            'calendar/allEvents.html.twig',
            [
                'userId' => $userId,
            ]
        );
    }

    /**
     * Action for controller
     * Showing single event
     *
     * @param Application $app
     *
     * @param String      $userId
     * @param String      $eventId
     *
     * @return mixed
     */
    public function showEvent(Application $app, $userId, $eventId)
    {
        return $app['twig']->render(
            'calendar/singleEvent.html.twig',
            [
                'userId' => $userId,
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
     * @param String      $userId
     * @param String      $eventId
     *
     * @return mixed
     */
    public function editEvent(Application $app, $userId, $eventId)
    {
        return $app['twig']->render(
            'calendar/editEvent.html.twig',
            [
                'userId' => $userId,
                'eventId' => $eventId,
            ]
        );
    }
}
