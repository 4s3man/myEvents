<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 11.05.18
 * Time: 16:55
 */

namespace Controller;

use DataManager\SessionMessagesDataManager;
use Form\CalendarType;
use Repositiory\CalendarRepository;
use Repositiory\UserCaledarRepository;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Utils\MyPaginatorShort;

/**
 * Class UserCalendarController
 */
class UserCalendarController implements ControllerProviderInterface
{
    /**
     * @param Application $app
     *
     * @return mixed|\Silex\ControllerCollection
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];

        //TODO change for get token from logged user || set in firewall
        $controller->get('/{userId}/index/page/{page}', [$this, 'userCalendarIndexAction'])
            ->bind('userCalendarIndex');
        $controller->match('/{userId}/add', [$this, 'addCalendarAction'])
            ->method('POST|GET')
            ->assert('userId', '[1-9]\d*')
            ->bind('calendarAdd');
        $controller->match('/{calendarId}/delete', [$this, 'deleteCalendarAction'])
            ->method('POST|GET')
            ->assert('calendarId', '[1-9]\d*')
            ->bind('calendarDelete');

        return $controller;
    }

    /**
     * @param Application $app
     * @param Int         $userId
     * @param Int         $page
     *
     * @return mixed
     */
    public function userCalendarIndexAction(Application $app, $userId, $page)
    {
        $calendarRepository = new UserCaledarRepository($app['db']);
        $paginator = new MyPaginatorShort(
            $calendarRepository->userCalendarJoinQuery(),
            3,
            'c.id',
            $page
        );

        return $app['twig']->render(
            'userCalendar/index.html.twig',
            [
                'pagerfanta' => $paginator->pagerfanta,
                'routeName' => 'userCalendarIndex',
                'userId' => $userId,
            ]
        );
    }

    /**
     * @param Application $app
     * @param int         $userId
     *
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function addCalendarAction(Application $app, $userId, Request $request)
    {
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
            'userCalendar/add.html.twig',
            [
                'form' => $form->createView(),
                'userId' => $userId,
            ]
        );
    }

    public function deleteCalendarAction(Application $app, $calendarId, Request $request)
    {
        //TODO get userId from logged user
        $loggedUserId = 1;

        $sessionMessages = new SessionMessagesDataManager($app['session']);
        $calendarRepository = new CalendarRepository($app['db']);
        $userCalendarRepository = new UserCaledarRepository($app['db']);

        $calendar = $calendarRepository->findOneById($calendarId);
        if (!$calendar) {
            $sessionMessages->recordNotFound();

            return $app->redirect($app['url_generator']->generate('userCalendarIndex' , ['userId' => $loggedUserId, 'page' => 1]), 301);
        }
        //TODO czy nie trzeba tutaj dodać walidacji formularza? assert w kontrolerze nie załatwia sprawy formularza !
        $form = $app['form.factory']->createBuilder(FormType::class, $calendar)->add('id', HiddenType::class)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() ) {
            $calendar  = $form->getData();
            $userCalendarRepository->delete($calendar, $loggedUserId);
            $sessionMessages->deleted();

            return $app->redirect($app['url_generator']->generate('userCalendarIndex' , ['userId' => $loggedUserId, 'page' => 1]), 301);
        }

        return $app['twig']->render(
            'userCalendar/delete.html.twig',
            [
                'calendarId' => $calendarId,
                'dataToDelete' => $calendar,
                'form' => $form->createView(),
            ]
        );

    }
}
