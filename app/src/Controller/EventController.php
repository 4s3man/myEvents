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
use Repositiory\EventRepository;
use Repositiory\MediaRepository;
use Repositiory\ParticipantRepository;
use Repositiory\TagRepository;
use Search\Criteria\TypeCriteria;
use Search\CriteriaBuilder\TypeCriteriaBuilder;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

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

        $controller->match('/{calendarId}/{eventId}', [$this, 'eventShowAction'])
            ->method('POST|GET')
            ->assert('calendarId', '[1-9]\d*')
            ->assert('eventId', '[1-9]\d*')
            ->bind('eventShow');

        $controller->match('/{calendarId}/{eventId}/edit', [$this, 'eventEditAction'])
            ->method('POST|GET')
            ->assert('calendarId', '[1-9]\d*')
            ->assert('eventId', '[1-9]\d*')
            ->bind('eventEdit');

        $controller->get('/{calendarId}/{eventId}/delete', [$this, 'eventDeleteAction'])
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
        $eventRepository = new EventRepository($app['db']);
        $tagRepository = new TagRepository($app['db']);
        $mediaRepository = new MediaRepository($app['db']);
        $sessionMessagesManager = new SessionMessagesDataManager($app['session']);

        //Todo get id from logged user
        $userId = 1;

        $event = [];
        $form = $app['form.factory']->CreateBuilder(
            EventType::class,
            $event,
            [
                'event_repository' => $eventRepository,
                'tag_repository' => $tagRepository,
                'media_repository' => $mediaRepository,
                'calendarId' => $calendarId,
                'userId' => $userId,
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
        $eventRepository = new EventRepository($app['db'], $calendarId);
        //TODO ostlować search form, ewentualnie potem bardziej doprany search
        //robić w końcu wgląd tych eventów czy edit i delete eventów najpierw?

        $queryParams = ['calendarId' => $calendarId, 'page' => $page];

        $form = $app['form.factory']
            ->createBuilder(EventSearchType::class)
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            //TODO pytanie tutaj nie mam żadnych walidatorów a robie if is valid jakie asserty do search form?
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
                //todo przekierowanie email został wysłany
//                $app->redirect($app['url_generator']->generate(''))
            }

            $signUpFormView = $form->createView();
        }

        $info = null;
        if (!$eventDataManager->seatsRemain() && $eventDataManager->getSignUp()) {
            $info = 'info.no_seats_left';
        }

        return $app['twig']->render(
            'event/ev-single.html.twig',
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
     * Edit event site
     *
     * @param Application $app
     *
     * @param String      $calendarId
     * @param String      $eventId
     *
     * @return mixed
     */
    public function eventEditAction(Application $app, $calendarId, $eventId, Request $request)
    {
        //todo teraz tutaj
        $eventRepository = new EventRepository($app['db']);
        $tagRepository = new TagRepository($app['db']);
        $mediaRepository = new MediaRepository($app['db']);
        $sessionMessagesManager = new SessionMessagesDataManager($app['session']);

        //Todo get id from logged user
        $userId = 1;

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
                'userId' => $userId,
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
            'event/ev-add.html.twig',
            [
                'form' => $form->createView(),
                'calendarId' => $calendarId,
            ]
        );
    }

    /**
     * Delete event
     *
     * @param Application $app
     *
     * @param String      $calendarId
     * @param String      $eventId
     *
     * @return mixed
     */
    public function eventDeleteAction(Application $app, $calendarId, $eventId)
    {
        //TODO edit event
        return $app['twig']->render(
            'event/ev-delete.html.twig',
            [
                'calendarId' => $calendarId,
                'eventId' => $eventId,
            ]
        );
    }
}
