<?php
/**
 * Created by PhpStorm.
 * User: Kuba Kułaga
 * Date: 29.04.18
 * Time: 10:38
 */

namespace Controller;

use DataManager\CalendarDataManager;
use DataManager\EventDataManager;
use DataManager\SessionMessagesDataManager;
use Form\CalendarType;
use Form\EventType;
use KGzocha\Searcher\Context\Doctrine\QueryBuilderSearchingContext;
use KGzocha\Searcher\Criteria\Collection\CriteriaCollection;
use KGzocha\Searcher\CriteriaBuilder\Collection\CriteriaBuilderCollection;
use KGzocha\Searcher\Searcher;
use KGzocha\Searcher\WrappedResultsSearcher;
use Repositiory\CalendarRepository;
use Repositiory\EventRepository;
use Repositiory\UserRepository;
use Search\Adapter\SearchingContextDoctrineDBALAdapter;
use Search\Criteria\TitleCriteria;
use Search\Criteria\TypeCriteria;
use Search\CriteriaBuilder\TitleCriteriaBuilder;
use Search\CriteriaBuilder\TypeCriteriaBuilder;
use Search\DataManager\EventSearchDataManager;
use Search\SearcherForPagerfanta;
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
        $controller->get('/{calendarId}/{date}', [$this, 'calendarShowAction'])
            ->assert('date', '[1-3]{1}[0-9]{3}-(0[1-9]|1[0-2])')
            ->bind('calendarShow');
        $controller->match('/{calendarId}/addEvent', [$this, 'eventAddAction'])
            ->method('POST|GET')
            ->bind('eventAdd');
        $controller->get('/{calendarId}/event/page/{page}', [$this, 'eventIndexAction'])
            ->bind('eventIndex');
        $controller->get('/{calendarId}/event/{eventId}', [$this, 'eventPageAction'])
            ->bind('eventShow');
        $controller->get('/{calendarId}/event/{eventId}/edit', [$this, 'eventEditAction'])
            ->bind('eventEdit');
        $controller->get('/{calendarId}/event/{eventId}/edit', [$this, 'eventDeleteAction'])
            ->bind('eventDelete');
        $controller->match('{calendarId}/index/page/{page}', [$this, 'calendarIndexAction'])
            ->method('POST|GET')
            ->assert('calendarId', '[1-9]\d*')
            ->bind('userIndex');
        $controller->get('{calendarId}/add', [$this, 'userAddAction'])
            ->bind('userAdd');
        $controller->match('/{calendarId}/edit', [$this, 'editCalendarAction'])
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
     * @param String      $date
     *
     * @return mixed
     */
    public function calendarShowAction(Application $app, $calendarId, $date)
    {
        $eventRepository = new EventRepository($app['db']);
        $calendarDataManager = new CalendarDataManager($eventRepository, $date);
        $calendar = $calendarDataManager->makeCalendarMonthPage();

        return $app['twig']->render(
            'calendar/calendar.html.twig',
            [
                'calendarId' => $calendarId,
                'calendar' => $calendar,
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
     * @param Request     $request
     *
     * @return mixed
     */
    public function eventAddAction(Application $app, $calendarId, Request $request)
    {
        $eventRepository = new EventRepository($app['db']);
        $sessionMessagesManager = new SessionMessagesDataManager($app['session']);

        $event = [];
        $form = $app['form.factory']->CreateBuilder(EventType::class, $event)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $eventDataManager = new EventDataManager($form->getData(), $calendarId);
            $eventRepository->save($eventDataManager->getEvent());
            $sessionMessagesManager->added();

            return $app
                ->redirect(
                    $app['url_generator']
                    ->generate(
                        'eventIndex',
                        ['calendarId' => $calendarId, 'page' => 1]
                    ),
                    301
                );
        }

        return $app['twig']->render(
            'calendar/addEvent.html.twig',
            [
                'form' => $form->createView(),
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
    public function eventIndexAction(Application $app, $calendarId, $page)
    {
        $eventRepository = new EventRepository($app['db']);
        $eventSearchDataManager = new EventSearchDataManager($eventRepository);
        //TODO posprzątać i zrobić formularz, pospinać formularz z data manager
        //porobić tak żeby criteria były spoko

        $query = $eventSearchDataManager->search();

        $paginator = new MyPaginatorShort(
            $query,
            '3',
            'e.id',
            $page
        );


        return $app['twig']->render(
            'calendar/eventIndex.html.twig',
            [
                'pagerfanta' => $paginator->pagerfanta,
                'routeName' => 'eventIndex',
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
    public function eventEditAction(Application $app, $calendarId, $eventId)
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
    public function calendarIndexAction(Application $app, $calendarId, $page = 1)
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
        //TODO get token from logged user
        $loggedUserId = 1;

        $sessionMessages = new SessionMessagesDataManager($app['session']);
        $calendarRepository = new CalendarRepository($app['db']);
        $calendar = $calendarRepository->findOneById($calendarId);

        if (!$calendar) {
            $sessionMessages->recordNotFound();

            return $app->redirect($app['url_generator']->generate('userCalendarIndex', ['userId' => $loggedUserId, 'page' => 1]), 301);
        }

        $form = $app['form.factory']->createBuilder(CalendarType::class, $calendar)->getForm();

        $form->handleRequest($request);

        $calendar = $form->getData();

        if ($form->isSubmitted() and $form->isValid()) {
            $calendarRepository->save($calendar);
            $sessionMessages->changed();

            return $app->redirect($app['url_generator']->generate('userCalendarIndex', ['userId' => $loggedUserId, 'page' => 1]), 301);
        }

        return $app['twig']->render(
            'userCalendar/edit.html.twig',
            [
                'calendarId' => $calendarId,
                'calendar' => $calendar,
                'form' => $form->createView(),
            ]
        );
    }
}
