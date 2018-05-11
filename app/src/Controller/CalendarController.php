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
        $controller->get('/{userId}', [$this, 'calendarAction'])
            ->bind('home');
        $controller->get('/{userId}/add', [$this, 'addAction'])
            ->bind('eventAdd');
        $controller->get('/{userId}/showAll/page/{page}', [$this, 'indexAction'])
            ->bind('eventsIndex');
        $controller->get('/{userId}/event/{eventId}', [$this, 'eventPageAction'])
            ->bind('eventShow');
        $controller->get('/{userId}/event/{eventId}/edit', [$this, 'editAction'])
            ->bind('eventEdit');

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
    public function calendarAction(Application $app, $userId)
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
    public function addAction(Application $app, $userId)
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
     * @param int         $userId
     * @param int         $page
     *
     * @return mixed
     */
    public function indexAction(Application $app, $userId, $page)
    {
        return $app['twig']->render(
            'calendar/index.html.twig',
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
    public function eventPageAction(Application $app, $userId, $eventId)
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
