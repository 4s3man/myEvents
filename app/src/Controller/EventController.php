<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 10.06.18
 * Time: 13:19
 */

namespace Controller;

use DataManager\EventDataManager;
use DataManager\SessionMessagesDataManager;
use Form\EventType;
use Form\Search\EventSearchType;
use Form\SignUpType;
use Repositiory\CalendarRepository;
use Repositiory\EventRepository;
use Repositiory\MediaRepository;
use Repositiory\ParticipantRepository;
use Repositiory\TagRepository;
use Search\Criteria\TypeCriteria;
use Search\CriteriaBuilder\TypeCriteriaBuilder;
use Security\Core\User\MyEventsUser;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class EventController
 */
class EventController implements ControllerProviderInterface
{
    /**
     * Routing settings
     *
     * @param Application $app
     *
     * @return mixed|ControllerCollection
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];

        $controller->match('/{calendarId}/add', [$this, 'eventAddAction'])
            ->method('POST|GET')
            ->assert('calendarId', '[1-9]\d*')
            ->bind('eventAdd');

        $controller->match('/{calendarId}/index/page/{page}', [$this, 'eventIndexAction'])
            ->method('POST|GET')
            ->assert('page', '[1-9]\d*')
            ->assert('calendarId', '[1-9]\d*')
            ->bind('eventIndex');

        $controller->match('/{calendarId}/{eventId}/show', [$this, 'eventShowAction'])
            ->method('POST|GET')
            ->assert('calendarId', '[1-9]\d*')
            ->assert('eventId', '[1-9]\d*')
            ->bind('eventShow');

        $controller->match('/{calendarId}/{eventId}/edit', [$this, 'eventEditAction'])
            ->method('POST|GET')
            ->assert('calendarId', '[1-9]\d*')
            ->assert('eventId', '[1-9]\d*')
            ->bind('eventEdit');

        $controller->match('/{calendarId}/{eventId}/delete', [$this, 'eventDeleteAction'])
            ->method('POST|GET')
            ->assert('calendarId', '[1-9]\d*')
            ->assert('eventId', '[1-9]\d*')
            ->bind('eventDelete');

        return $controller;
    }

    /**
     * Adding event site
     *
     * @param Application $app
     *
     * @param int         $calendarId
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Doctrine\DBAL\ConnectionException
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function eventAddAction(Application $app, $calendarId, Request $request)
    {
        $token = $app['security.token_storage']->getToken();
        $loggedUserId = $token->getUser()->getId();

        if (!$app['security.authorization_checker']->isGranted('calendar_any_user', $calendarId)) {
            return $app->redirect($app['url_generator']->generate('userCalendarIndex', ['userId' => $loggedUserId, 'page' => 1]));
        }

        $eventRepository = new EventRepository($app['db'], $calendarId);
        $tagRepository = new TagRepository($app['db']);
        $mediaRepository = new MediaRepository($app['db']);
        $sessionMessagesManager = new SessionMessagesDataManager($app['session']);

        $event = [];
        $form = $app['form.factory']->CreateBuilder(
            EventType::class,
            $event,
            [
                'event_repository' => $eventRepository,
                'tag_repository' => $tagRepository,
                'media_repository' => $mediaRepository,
                'calendarId' => $calendarId,
                'userId' => $loggedUserId,
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
            'event/ev-add.html.twig',
            [
                'form' => $form->createView(),
                'calendarId' => $calendarId,
                'userId' => $loggedUserId,
            ]
        );
    }

    /**
     * Show list of calendar events
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
        $token = $app['security.token_storage']->getToken();
        $user = $token->getUser();
        $loggedUserId = $user instanceof MyEventsUser ? $user->getId() : null;

        $calendarRepository = new CalendarRepository($app['db']);
        $calendar = $calendarRepository->findOneById($calendarId);
        if (!$calendar) {
            return new Response($app['twig']->render('errors/404.html.twig', ['userId' => $loggedUserId]), 404);
        }

        $eventRepository = new EventRepository($app['db'], $calendarId);
        $tagRepository = new TagRepository($app['db']);
        $queryParams = ['calendarId' => $calendarId, 'page' => $page];

        $form = $app['form.factory']
            ->createBuilder(EventSearchType::class, [], ['tag_repository' => $tagRepository])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $paginator = $eventRepository->getSearchedAndPaginatedRecords($queryParams, $form->getData());
        } else {
            $paginator = $eventRepository->getSearchedAndPaginatedRecords($queryParams);
        }

        return $app['twig']->render(
            'event/ev-index.html.twig',
            [
                'pagerfanta' => $paginator,
                'routeName' => 'eventIndex',
                'calendarId' => $calendarId,
                'form' => $form->createView(),
                'userId' => $loggedUserId,
            ]
        );
    }

    /**
     * Show single event site
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
        $token = $app['security.token_storage']->getToken();
        $user = $token->getUser();
        $loggedUserId = $user instanceof MyEventsUser ? $user->getId() : null;

        $eventRepository = new EventRepository($app['db'], $calendarId);
        $participantRepository = new ParticipantRepository($app['db'], $eventId);

        $event = $eventRepository->findOneById($eventId);
        if (!$event) {
            return new Response($app['twig']->render('errors/404.html.twig', ['userId' => $loggedUserId]), 404);
        }
        $eventDataManager = new EventDataManager(
            $event,
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
                $eventDataManager->decrementSeats();
                $participantRepository->save($form->getData(), $eventDataManager->getEvent());
                $sessionMessagesMenager->signedUp();

                return $app->redirect($app['url_generator']->generate('eventShow', ['eventId' => $eventId, 'calendarId' => $calendarId]));
            }

            $signUpFormView = $form->createView();
        }

        return $app['twig']->render(
            'event/ev-single.html.twig',
            [
                'event' => $eventDataManager->makeEvent(),
                'seatsRemains' => $eventDataManager->seatsRemain(),
                'signUp' => $eventDataManager->getSignUp(),
                'form' => $signUpFormView,
                'calendarId' => $calendarId,
                'eventId' => $eventId,
                'userId' => $loggedUserId,
            ]
        );
    }

    /**
     * Edit event site
     *
     * @param Application $app
     *
     * @param int         $calendarId
     * @param int         $eventId
     *
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function eventEditAction(Application $app, $calendarId, $eventId, Request $request)
    {
        $sessionMessagesManager = new SessionMessagesDataManager($app['session']);
        $token = $app['security.token_storage']->getToken();
        $loggedUserId = $token->getUser()->getId();
        if (!$app['security.authorization_checker']->isGranted('calendar_any_user', $calendarId)) {
            return $app->redirect($app['url_generator']->generate('userCalendarIndex', ['userId' => $loggedUserId, 'page' => 1]));
        }

        $eventRepository = new EventRepository($app['db'], $calendarId);
        $tagRepository = new TagRepository($app['db']);
        $mediaRepository = new MediaRepository($app['db']);

        $eventRepository->getLinkedTagsById($eventId);
        $event = $eventRepository->findOneById($eventId);

        if (!$event) {
            $sessionMessagesManager->recordNotFound();

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

        $form = $app['form.factory']->CreateBuilder(
            EventType::class,
            $event,
            [
                'event_repository' => $eventRepository,
                'tag_repository' => $tagRepository,
                'media_repository' => $mediaRepository,
                'calendarId' => $calendarId,
                'userId' => $loggedUserId,
            ]
        )->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $eventRepository->save($form->getData(), $calendarId);
            $sessionMessagesManager->changed();

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
            'event/ev-edit.html.twig',
            [
                'form' => $form->createView(),
                'calendarId' => $calendarId,
                'userId' => $loggedUserId,
            ]
        );
    }

    /**
     * Delete event
     *
     * @param Application $app
     *
     * @param int         $calendarId
     * @param int         $eventId
     *
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    public function eventDeleteAction(Application $app, $calendarId, $eventId, Request $request)
    {
        $sessionMessagesManager = new SessionMessagesDataManager($app['session']);
        $token = $app['security.token_storage']->getToken();
        $loggedUserId = $token->getUser()->getId();

        if (!$app['security.authorization_checker']->isGranted('calendar_any_user', $calendarId)) {
            return $app->redirect($app['url_generator']->generate('userCalendarIndex', ['userId' => $loggedUserId, 'page' => 1]));
        }
        $eventRepository = new EventRepository($app['db'], $calendarId);

        $event = $eventRepository->findOneById($eventId);
        if (!$event) {
            $sessionMessagesManager->recordNotFound();

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

        $form = $app['form.factory']->createBuilder(FormType::class, $event)->add('id', HiddenType::class)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sessionMessagesManager->deleted();
            $eventRepository->delete($eventId);

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
            'event/ev-delete.html.twig',
            [
                'form' => $form->createView(),
                'dataToDelete' => $event,
                'calendarId' => $calendarId,
                'eventId' => $eventId,
                'userId' => $loggedUserId,
            ]
        );
    }
}
