<?php
/**
 * Created by PhpStorm.
 * User: Kuba Kułaga
 * Date: 29.04.18
 * Time: 10:38
 */

namespace Controller;

use DataManager\CalendarDataManager;
use DataManager\SessionMessagesDataManager;
use Doctrine\DBAL\Types\IntegerType;
use Form\CalendarType;
use Form\LinkUserCalendarType;
use Form\Search\UserSearchType;
use Form\UserRoleType;
use Repositiory\CalendarRepository;
use Repositiory\EventRepository;
use Repositiory\UserCaledarRepository;
use Repositiory\UserRepository;
use Search\Criteria\TypeCriteria;
use Search\CriteriaBuilder\TypeCriteriaBuilder;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;

use Form\Search\SearchType;

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
            ->assert('calendarId', '[1-9]\d*')
            ->assert('date', '[1-3]{1}[0-9]{3}-(0[1-9]|1[0-2])')
            ->bind('calendarShow');

        $controller->match('/user/{userId}/add', [$this, 'addCalendarAction'])
            ->method('POST|GET')
            ->assert('userId', '[1-9]\d*')
            ->bind('calendarAdd');

        $controller->match('/{calendarId}/edit', [$this, 'editCalendarAction'])
            ->method('POST|GET')
            ->assert('calendarId', '[1-9]\d*')
            ->bind('calendarEdit');

        $controller->match('/{calendarId}/delete', [$this, 'deleteCalendarAction'])
            ->method('POST|GET')
            ->assert('calendarId', '[1-9]\d*')
            ->bind('calendarDelete');

//        $controller->match('/user/{userId}/index/page/{page}', [$this, 'userCalendarIndexAction'])
//            ->method('POST|GET')
//            ->assert('userId', '[1-9]\d*')
//            ->assert('page', '[1-9]\d*')
//            ->bind('userCalendarIndex');

        return $controller;
    }

    /**
     * Show Calendar with events
     *
     * @param Application $app
     *
     * @param int         $calendarId
     *
     * @param string      $date       format 'Y-m'
     *
     * @return mixed
     *
     * @throws \Exception
     */
    public function calendarShowAction(Application $app, $calendarId, $date)
    {
        //todo get id from logged user
        $userId = 4;

        //TODO dodać styl dla świąt
        $eventRepository = new EventRepository($app['db'], (int) $calendarId);
        $calendarDataManager = new CalendarDataManager($eventRepository, $date);
        $calendarMonthPage = $calendarDataManager->makeCalendarMonthPage();

        return $app['twig']->render(
            'calendar/calendar-show.html.twig',
            [
                'nextDate' => $calendarDataManager->getNextMonth()->format('Y-m'),
                'prevDate' => $calendarDataManager->getPrevMonth()->format('Y-m'),
                'calendarId' => $calendarId,
                'calendar' => $calendarMonthPage,
                'userId' => 4,
            ]
        );
    }


    /**
     *
     * @param Application $app
     *
     * @param Int         $userId
     *
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function addCalendarAction(Application $app, $userId, Request $request)
    {
        //TODO get id from logged user

        $sessionMessages = new SessionMessagesDataManager($app['session']);
        $userCalendarRepository = new UserCaledarRepository($app['db']);
        $calendar = [];
        $form = $app['form.factory']->createBuilder(CalendarType::class, $calendar)->getForm();

        $form->handleRequest($request);

        $calendar = $form->getData();

        if ($form->isSubmitted() && $form->isValid()) {
            $userCalendarRepository->save($calendar, $userId);
            $sessionMessages->added();

            return $app->redirect($app['url_generator']->generate('userCalendarIndex', ['userId' => $userId, 'page' => 1]), 301);
        }

        return $app['twig']->render(
            'calendar/calendar-add.html.twig',
            [
                'form' => $form->createView(),
                'userId' => $userId,
            ]
        );
    }

    /**
     *
     * @param Application $app
     * @param int         $calendarId
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editCalendarAction(Application $app, $calendarId, Request $request)
    {
        //TODO get token from logged user
        $userId = 4;

        $sessionMessages = new SessionMessagesDataManager($app['session']);
        $calendarRepository = new CalendarRepository($app['db']);
        $calendar = $calendarRepository->findOneById($calendarId);

        if (!$calendar) {
            $sessionMessages->recordNotFound();

            return $app->redirect($app['url_generator']->generate('userCalendarIndex', ['userId' => $loggedUserId, 'page' => 1]), 301);
        }

        $form = $app['form.factory']->createBuilder(CalendarType::class, $calendar)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() and $form->isValid()) {
            $calendar = $form->getData();
            $calendarRepository->save($calendar);
            $sessionMessages->changed();

            return $app->redirect($app['url_generator']->generate('userCalendarIndex', ['userId' => $loggedUserId, 'page' => 1]), 301);
        }

        return $app['twig']->render(
            'calendar/calendar-edit.html.twig',
            [
                'userId' => $userId,
                'calendarId' => $calendarId,
                'calendar' => $calendar,
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Delete calendar
     *
     * @param Application $app
     * @param int         $calendarId
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function deleteCalendarAction(Application $app, $calendarId, Request $request)
    {
        //TODO get userId from logged user
        $userId = 4;

        $sessionMessages = new SessionMessagesDataManager($app['session']);
        $calendarRepository = new CalendarRepository($app['db']);
        $userCalendarRepository = new UserCaledarRepository($app['db']);

        $calendar = $calendarRepository->findOneById($calendarId);
        if (!$calendar) {
            $sessionMessages->recordNotFound();

            return $app->redirect($app['url_generator']->generate('userCalendarIndex', ['page' => 1]), 301);
        }
        $form = $app['form.factory']->createBuilder(FormType::class, $calendar)->add('id', HiddenType::class)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $calendar  = $form->getData();
            $userCalendarRepository->delete($calendar, $loggedUserId);
            $sessionMessages->deleted();

            return $app->redirect($app['url_generator']->generate('userCalendarIndex', ['page' => 1]), 301);
        }

        return $app['twig']->render(
            'calendar/calendar-delete.html.twig',
            [
                'userId' => $userId,
                'calendarId' => $calendarId,
                'dataToDelete' => $calendar,
                'form' => $form->createView(),
            ]
        );
    }
}
