<?php
/**
 * Created by PhpStorm.
 * User: Kuba Kułaga
 * Date: 29.04.18
 * Time: 10:38
 */

namespace Controller;

use Repositiory\UserRepository;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Utils\MyPaginatorShort;

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

        //TODO przenieść do userCalendarController
        $controller->get('{calendarId}/index/page/{page}', [$this, 'indexAction'])
            ->method('POST|GET')
            ->bind('userIndex');
        $controller->get('{calendarId}/add', [$this, 'userAddAction'])
            ->bind('userAdd');


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

    /**
     * List users and available actions
     *
     * @param Application $app
     *
     * @param int $calendarId
     * @param int $page
     *
     * @return mixed
     */
    public function indexAction(Application $app, $calendarId, $page = 1)
    {
        $userRepository = new UserRepository($app['db']);

        $paginator = new MyPaginatorShort($userRepository->queryAll(), 5, $page);

        return $app['twig']->render(
            'user/index.html.twig',
            [
                'calendarId' => $calendarId,
                'pagerfanta' => $paginator->pagerfanta,
                'routeName' => 'userIndex',
                'users' => $paginator->pagerfanta->getCurrentPageResults(),
            ]
        );
    }

    /**
     * Add user to this calendar associated witch calendarId
     *
     * @param Application $app
     * @param int $calendarId
     *
     * @return mixed
     */
    public function userAddAction(Application $app, $calendarId)
    {
        return $app['twig']->render(
            'user/add.html.twig',
            [
                'calendarId' => $calendarId,
            ]
        );
    }
}
