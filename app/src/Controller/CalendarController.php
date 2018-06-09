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
use Form\LinkUserCalendarType;
use Form\Search\EventSearchType;
use Form\SignUpType;
use Form\UserRoleType;
use Repositiory\CalendarRepository;
use Repositiory\EventRepository;
use Repositiory\ParticipantRepository;
use Repositiory\TagRepository;
use Repositiory\UserCaledarRepository;
use Repositiory\UserRepository;
use Search\Criteria\TitleCriteria;
use Search\Criteria\TypeCriteria;
use Search\CriteriaBuilder\TitleCriteriaBuilder;
use Search\CriteriaBuilder\TypeCriteriaBuilder;
use Search\DataManager\EventSearchDataManager;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
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
            ->assert('calendarId', '[1-9]\d*')
            ->assert('date', '[1-3]{1}[0-9]{3}-(0[1-9]|1[0-2])')
            ->bind('calendarShow');
        $controller->match('/{calendarId}/addEvent', [$this, 'eventAddAction'])
            ->method('POST|GET')
            ->assert('calendarId', '[1-9]\d*')
            ->bind('eventAdd');
        $controller->match('/{calendarId}/event/index/page/{page}', [$this, 'eventIndexAction'])
            ->method('POST|GET')
            ->assert('page', '[1-9]\d*')
            ->assert('calendarId', '[1-9]\d*')
            ->bind('eventIndex');
        $controller->match('/{calendarId}/event/{eventId}', [$this, 'eventShowAction'])
            ->method('POST|GET')
            ->assert('calendarId', '[1-9]\d*')
            ->assert('eventId', '[1-9]\d*')
            ->bind('eventShow');
        $controller->get('/{calendarId}/event/{eventId}/edit', [$this, 'eventEditAction'])
            ->assert('calendarId', '[1-9]\d*')
            ->assert('eventId', '[1-9]\d*')
            ->bind('eventEdit');
        $controller->get('/{calendarId}/event/{eventId}/delete', [$this, 'eventDeleteAction'])
            ->assert('calendarId', '[1-9]\d*')
            ->assert('eventId', '[1-9]\d*')
            ->bind('eventDelete');
        $controller->match('{calendarId}/user/index/page/{page}', [$this, 'calendarUserIndexAction'])
            ->method('POST|GET')
            ->assert('calendarId', '[1-9]\d*')
            ->assert('page', '[1-9]\d*')
            ->bind('userIndex');
        $controller->match('{calendarId}/addUser', [$this, 'userAddAction'])
            ->method('POST|GET')
            ->assert('calendarId', '[1-9]\d*')
            ->bind('userAdd');
        $controller->match('/{calendarId}/{userCalendarId}/editUser', [$this, 'userEditAction'])
            ->method('POST|GET')
            ->assert('userCalendarId', '[1-9]\d*')
            ->bind('userEdit');
        $controller->match('/{calendarId}/{userCalendarId}/deleteUser', [$this, 'userDeleteAction'])
            ->method('POST|GET')
            ->assert('userCalendarId', '[1-9]\d*')
            ->bind('userDelete');
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
        $eventRepository = new EventRepository($app['db'], (int) $calendarId);
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
        $tagRepository = new TagRepository($app['db']);
        $sessionMessagesManager = new SessionMessagesDataManager($app['session']);

        $event = [];
        $form = $app['form.factory']->CreateBuilder(
            EventType::class,
            $event,
            [
                'event_repository' => $eventRepository,
                'tag_repository' => $tagRepository,
            ]
        )->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $eventRepository->save($form->getData(), $calendarId);
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
     * @param Request     $request
     *
     * @return mixed
     */
    public function eventIndexAction(Application $app, $calendarId, $page, Request $request)
    {
        $eventRepository = new EventRepository($app['db'], $calendarId);
        //TODO OSTATNIE co z tym wyszukiwaniem, zrobic wlasne
        //ostlować search form
        //robić w końcu wgląd tych eventów czy edit i delete eventów najpierw?
        $query = $eventRepository->queryAll();

        $form = $app['form.factory']
            ->createBuilder(EventSearchType::class)
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            $eventSearchDataManager = new EventSearchDataManager(
                [
                   new TitleCriteriaBuilder('e'),
                ],
                [
                    new TitleCriteria($data['title']),
                ],
                $eventRepository->queryAll()
            );

            $query = $eventSearchDataManager->search();
        }

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
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Action for controller
     * Showing single event
     *
     * @param Application $app
     *
     * @param int         $calendarId
     * @param int         $eventId
     *
     * @param Request     $request
     *
     * @return mixed
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function eventShowAction(Application $app, $calendarId, $eventId, Request $request)
    {
        //TODO spytać się jak by to lepiej
        //TODO na koniec sign up przez potwierdzenie email
        $eventRepository = new EventRepository($app['db']);
        $participantRepository = new ParticipantRepository($app['db']);
        $eventDataManager = new EventDataManager(
            $eventRepository->findOneById($eventId),
            $calendarId
        );

        $signUpFormView = null;
        if ($eventDataManager->getSignUp() && $eventDataManager->seatsRemain()) {
            $signUp = [];
            $form = $app['form.factory']->createBuilder(SignUpType::class, $signUp, ['repository' => $participantRepository])
                ->getForm();

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                $sessionMessagesMenager = new SessionMessagesDataManager($app['session']);

                $participantRepository->save($form->getData(), $eventDataManager->getEvent());
                $sessionMessagesMenager->signedUp();
            }

            $signUpFormView = $form->createView();
        }

        $info = null;
        if (!$eventDataManager->seatsRemain() && $eventDataManager->getSignUp()) {
            $info = 'info.no_seats_left';
        }

        return $app['twig']->render(
            'calendar/singleEvent.html.twig',
            [
                'event' => $eventDataManager->makeEvent(),
                'signUp' => $signUpFormView,
                'info' => $info,
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
    public function calendarUserIndexAction(Application $app, $calendarId, $page = 1)
    {
        //TODO zrobić index urzytkowników kalendarza
        $userCalendarRepository = new UserCaledarRepository($app['db']);
        $paginator = $userCalendarRepository->getPaginatedUserAndRolesByCalendarId($calendarId, $page);

        return $app['twig']->render(
            'calendar/userIndex.html.twig',
            [
                'calendarId' => $calendarId,
                'pagerfanta' => $paginator,
                'routeName' => 'userIndex',
            ]
        );
    }

    //TODO dodawanie usera do kalendarza
    /**
     * Add user to this calendar associated witch calendarId
     *
     * @param Application $app
     *
     * @param int         $calendarId
     *
     * @return mixed
     */
    public function userAddAction(Application $app, $calendarId, Request $request)
    {
        $sessionMessagesDataManager = new SessionMessagesDataManager($app['session']);
        $userRepository = new UserRepository($app['db']);
        $userCalendar = [];
        $form = $app['form.factory']
            ->createBuilder(LinkUserCalendarType::class, $userCalendar, ['repository' => $userRepository])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userCalendarRepository = new UserCaledarRepository($app['db']);
            $userCalendar = $form->getData();
            $user = $userRepository->findOneByEmail($userCalendar['email']);

            if ($userCalendarRepository->isLinked($user['id'], $calendarId)) {
                $sessionMessagesDataManager->alereadyLinked();
            } else {
                $userCalendarRepository->linkUserToCalendar(
                    $user['id'],
                    $userCalendar['user_role'],
                    $calendarId
                );
                $sessionMessagesDataManager->added();

                return $app->redirect($app['url_generator']->generate(
                    'userIndex',
                    [
                        'calendarId' => $calendarId,
                        'page' => 1,
                    ]
                ),
                    301
                );
            }
        }

        return $app['twig']->render(
            'calendar/addUser.html.twig',
            [
                'form' => $form->createView(),
                'calendarId' => $calendarId,
            ]
        );
    }

    /**
     * @param Application $app
     *
     * @param int         $userCalendarId
     *
     * @param Request     $request
     *
     * @return mixed
     */
    public function userEditAction(Application $app, $calendarId, $userCalendarId, Request $request)
    {
        $userCalendarRepository = new UserCaledarRepository($app['db']);
        $sessionDataManager = new SessionMessagesDataManager($app['session']);

        $userType = $userCalendarRepository->findOneById($userCalendarId);
        $form = $app['form.factory']
            ->createBuilder(UserRoleType::class, $userType)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userCalendarRepository->updateUserRoleFoundById($userCalendarId, $form->getData());
            $sessionDataManager->changed();

            return $app->redirect($app['url_generator']->generate(
                'userIndex',
                [
                    'calendarId' => $calendarId,
                    'page' => 1,
                ]
            ),
                301
            );
        }

        return $app['twig']->render(
            'calendar/editUser.html.twig',
            [
                'form' => $form->createView(),
                'userCalendarId' => $userCalendarId,
            ]
        );
    }

    public function userDeleteAction(Application $app, $calendarId, $userCalendarId, Request $request)
    {
        $userCalendarRepository = new UserCaledarRepository($app['db']);
        $userCalendar = $userCalendarRepository->findLinkedUserById($userCalendarId);
        $sessionDataManager = new SessionMessagesDataManager($app['session']);

        if (!$userCalendar) {
            $sessionDataManager->recordNotFound();
            return $app->redirect($app['url_generator']->generate(
                'userIndex',
                [
                    'calendarId' => $calendarId,
                    'page' => 1,
                ]
            ),
                301
            );
        }
            $form = $app['form.factory']
                ->createBuilder(FormType::class, $userCalendar)
                ->add('id', HiddenType::class)
                ->getForm();
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                $form->getData();
                $userCalendarRepository->deleteLink($userCalendarId);
                $sessionDataManager->deleted();
                return $app->redirect($app['url_generator']->generate(
                    'userIndex',
                    [
                        'calendarId' => $calendarId,
                        'page' => 1,
                    ]
                ),
                    301
                );
            }

        return $app['twig']->render(
            'calendar/deleteUser.html.twig',
            [
                'form' => $form->createView(),
                'userCalendarId' => $userCalendarId,
                'dataToDelete' => $userCalendar,
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

        if ($form->isSubmitted() and $form->isValid()) {
            $calendar = $form->getData();
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
