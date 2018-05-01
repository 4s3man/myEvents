<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 29.04.18
 * Time: 10:38
 */

namespace Model;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Silex\ControllerCollection;

class CalendarController implements ControllerProviderInterface
{
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

    public function showCalendar(Application $app, $userId)
    {
        return $app['twig']->render(
            'calendar/calendar.html.twig',
            [
                'userId' => $userId,
            ]
        );
    }

    public function addEvent(Application $app, $userId)
    {
        return $app['twig']->render(
            'calendar/addEvent.html.twig',
            [
                'userId' => $userId,
            ]
        );
    }

    public function showAllEvents(Application $app, $userId)
    {
        return $app['twig']->render(
            'calendar/allEvents.html.twig',
            [
                'userId' => $userId,
            ]
        );
    }

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
