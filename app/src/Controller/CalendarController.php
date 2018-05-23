<?php
/**
 * Created by PhpStorm.
 * User: Kuba Kułaga
 * Date: 29.04.18
 * Time: 10:38
 */

namespace Controller;

use Form\CalendarType;
use Repositiory\CalendarRepository;
use Repositiory\UserRepository;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
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
            ->bind('calendarShow');
        $controller->get('/{calendarId}/addEvent', [$this, 'addAction'])
            ->bind('eventAdd');
        $controller->get('/{calendarId}/events/page/{page}', [$this, 'eventsIndexAction'])
            ->bind('eventsIndex');
        $controller->get('/{calendarId}/event/{eventId}', [$this, 'eventPageAction'])
            ->bind('eventShow');
        $controller->get('/{calendarId}/event/{eventId}/edit', [$this, 'editAction'])
            ->bind('eventEdit');
        $controller->get('{calendarId}/index/page/{page}', [$this, 'indexAction'])
            ->method('POST|GET')
            ->assert('calendarId', '[1-9]\d*')
            ->bind('userIndex');
        $controller->get('{calendarId}/add', [$this, 'userAddAction'])
            ->bind('userAdd');
        $controller->get('/{calendarId}/edit', [$this, 'editCalendarAction'])
            ->method('POST|GET')
            ->assert('calendarId', '[1-9]\d*')
            ->bind('calendarEdit');

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
     * @param int         $calendarId
     * @param int         $page
     *
     * @return mixed
     */
    public function indexAction(Application $app, $calendarId, $page = 1)
    {
        $userRepository = new UserRepository($app['db']);

        $paginator = new MyPaginatorShort($userRepository->queryAll(), 5, 'id', $page);

        return $app['twig']->render(
            'calendar/userIndex.html.twig',
            [
                'calendarId' => $calendarId,
                'pagerfanta' => $paginator->pagerfanta,
                'routeName' => 'userIndex',
            ]
        );
    }

    /**
     * Add user to this calendar associated witch calendarId
     *
     * @param Application $app
     *
     * @param int         $calendarId
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

    /**
     * @param Application $app
     * @param int         $calendarId
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editCalendarAction(Application $app, $calendarId, Request $request)
    {
        $calendarRepository = new CalendarRepository($app['db']);
        $calendar = $calendarRepository->findOneById($calendarId);

        if (!$calendar) {
            $app['session']->getFlashBag()->add(
                'message',
                [
                    'type' => 'errror',
                    'message' => 'message.record_not_found',
                ]
            );
            //TODO get token from logged user
            return $app->redirect($app['url_generator']->generate('userCalendarIndex'), 301);
        }

        $form = $app['form.factory']->createBuilder(CalendarType::class, $calendar)->getForm();

        $form->handleRequest($request);

        $calendar = $form->getData();

        if ($form->isSubmitted() and $form->isValid()) {
            $calendarRepository->save($calendar);
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.calendar_added',
                ]
            );
            //TODO get token from logged user
            return $app->redirect($app['url_generator']->generate('userCalendarIndex', ['userId' => 1, 'page' => 1]), 301);
        }

        return $app['twig']->render(
            'userCalendar/add.html.twig',
            [
                'calendar' => $calendar,
                'form' => $form->createView(),
            ]
        );
    }
}
