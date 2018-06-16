<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 07.06.18
 * Time: 10:18
 */

namespace Controller;

use DataManager\SessionMessagesDataManager;
use Form\MediaType;
use Form\Search\SearchType;
use Form\TitleType;
use Repositiory\MediaRepository;
use Service\FileUploader;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;
use Utils\MyPaginatorShort;

/**
 * Class MediaController
 */
class MediaController implements ControllerProviderInterface
{
    /**
     * Sets routing
     *
     * @param Application $app
     *
     * @return mixed|\Silex\ControllerCollection
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];

        //TODO change for get token from logged user || set in firewall
        $controller->match('/{userId}/add', [$this, 'addMediaAction'])
            ->method('POST|GET')
            ->assert('userId', '[1-9]\d*')
            ->assert('page', '[1-9]\d*')
            ->bind('userAddMedia');

        $controller->match('/calendar/{calendarId}/add', [$this, 'calendarAddMediaAction'])
            ->method('POST|GET')
            ->assert('calendarId', '[1-9]\d*')
            ->assert('page', '[1-9]\d*')
            ->bind('calendarAddMedia');

        $controller->match('/user/{userId}/{mediaId}/edit', [$this, 'editMediaAction'])
            ->assert('userId', '[1-9]\d*')
            ->assert('mediaId', '[1-9]\d*')
            ->method('POST|GET')
            ->bind('editMedia');

        $controller->match('/user/{userId}/{mediaId}/delete', [$this, 'deleteMediaAction'])
            ->assert('userId', '[1-9]\d*')
            ->assert('mediaId', '[1-9]\d*')
            ->method('POST|GET')
            ->bind('deleteMedia');

        $controller->match('/user/{userId}/index/page/{page}', [$this, 'userMediaIndexAction'])
            ->assert('userId', '[1-9]\d*')
            ->assert('page', '[1-9]\d*')
            ->bind('userMediaIndex');


        $controller->match('/event/{calendarId}/index/page/{page}', [$this, 'eventMediaIndexAction'])
            ->method('POST|GET')
            ->assert('page', '[1-9]\d*')
            ->bind('eventMediaIndex');

        $controller->match('/calendar/{calendarId}/index/page/{page}', [$this, 'calendarMediaIndexAction'])
            ->method('POST|GET')
            ->assert('page', '[1-9]\d*')
            ->bind('calendarMediaIndex');

        $controller->match('/calendar/{calendarId}/{mediaId}/edit', [$this, 'editCalendarMediaAction'])
            ->assert('calendarId', '[1-9]\d*')
            ->assert('mediaId', '[1-9]\d*')
            ->method('POST|GET')
            ->bind('editCalendarMedia');

        $controller->match('/calendar/{calendarId}/{mediaId}/delete', [$this, 'deleteCalendarMediaAction'])
            ->assert('calendarId', '[1-9]\d*')
            ->assert('mediaId', '[1-9]\d*')
            ->method('POST|GET')
            ->bind('deleteCalendarMedia');



        return $controller;
    }

    /**
     *
     * @param Application $app
     *
     * @param Request     $request
     *
     * @return mixed
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function addMediaAction(Application $app, $userId, Request $request)
    {
        //TODO get id from logged user

        $media = [];
        $form = $app['form.factory']->createBuilder(MediaType::class, $media)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $sessionMessagesManager = new SessionMessagesDataManager($app['session']);
            $fileUploader = new FileUploader($app['config.photos_directory']);
            $mediaRepository = new MediaRepository($app['db']);

            $photo  = $form->getData();

            $fileName = $fileUploader->upload($photo['photo']);

            $photo['photo'] = $fileName;

            $mediaRepository->saveToUser($photo, $userId);

            $sessionMessagesManager->added();

            return $app->redirect($app['url_generator']->generate('userMediaIndex', ['userId' => $userId, 'page' => 1]));
        }

        return $app['twig']->render(
            'media/md-add_toUser.html.twig',
            [
                'form' => $form->createView(),
                'userId' => $userId,
            ]
        );
    }

    /**
     *
     * @param Application $app
     *
     * @param int         $calendarId
     *
     * @param Request     $request
     *
     * @return mixed
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function calendarAddMediaAction(Application $app, $calendarId, Request $request)
    {
        //TODO GET ID FROM LOGGED USER
        $userId = 4;
        $sessionMessagesManager = new SessionMessagesDataManager($app['session']);

        $media = [];
        $form = $app['form.factory']->createBuilder(MediaType::class, $media)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photo  = $form->getData();
            $fileUploader = new FileUploader($app['config.photos_directory']);
            $fileName = $fileUploader->upload($photo['photo']);
            $photo['photo'] = $fileName;
            $mediaRepository = new MediaRepository($app['db']);
            $mediaRepository->saveToCalendar($photo, $calendarId);

            $sessionMessagesManager->added();

            return $app->redirect(
                $app['url_generator']->generate('calendarMediaIndex', ['calendarId' => $calendarId, 'page' => 1]),
                301
            );
        }

        return $app['twig']->render(
            'media/md-add_toCalendar.html.twig',
            [
                'form' => $form->createView(),
                'userId' => $userId,
            ]
        );
    }

    public function editMediaAction(Application $app, $userId, $mediaId, Request $request)
    {
        //todo zeminić nazwę na editUserMedia, i zrobić inny kontroler dla mediów kalendarza
        //tak usuwanie jedynie linków media muszą być usuwane osobno
        $sessionMessagesManager = new SessionMessagesDataManager($app['session']);
        $mediaRepository = new MediaRepository($app['db']);

        $media = $mediaRepository->findOneById($mediaId);

        if (!$media) {
            $sessionMessagesManager->recordNotFound();

            return $app->redirect($app['url_generator']->generate('userMediaIndex', ['userId' => $userId, 'page' => 1]));
        }

        $form = $app['form.factory']->createBuilder(TitleType::class, $media)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mediaRepository->saveToUser($form->getData(), $userId);
            $sessionMessagesManager->changed();

            return $app->redirect($app['url_generator']->generate('userMediaIndex', ['userId' => $userId, 'page' => 1]));
        }

        return $app['twig']->render(
            'media/md-edit.html.twig',
            [
                'form' => $form->createView(),
                'userId' => $userId,
            ]
        );
    }

    public function editCalendarMediaAction(Application $app, $calendarId, $mediaId, Request $request)
    {
        //todo GET LOGGED USER ID
        $userId = 4;

        $sessionMessagesManager = new SessionMessagesDataManager($app['session']);
        $mediaRepository = new MediaRepository($app['db']);

        $media = $mediaRepository->findOneById($mediaId);

        if (!$media) {
            $sessionMessagesManager->recordNotFound();

            return $app->redirect($app['url_generator']->generate('calendarMediaIndex', ['calendarId' => $calendarId, 'page' => 1]));
        }

        $form = $app['form.factory']->createBuilder(TitleType::class, $media)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mediaRepository->saveToCalendar($form->getData(), $calendarId);
            $sessionMessagesManager->changed();

            return $app->redirect($app['url_generator']->generate('calendarMediaIndex', ['calendarId' => $calendarId, 'page' => 1]));
        }

        return $app['twig']->render(
            'media/md-edit.html.twig',
            [
                'form' => $form->createView(),
                'userId' => $userId,
                'calendarId' => $calendarId,
            ]
        );
    }

    public function deleteCalendarMediaAction(Application $app, $calendarId, $mediaId, Request $request)
    {
        //TODO GET ID FROM LOGGED USER
        $userId = 4;

        $sessionMessagesManager = new SessionMessagesDataManager($app['session']);
        $mediaRepository = new MediaRepository($app['db']);

        $media = $mediaRepository->findOneById($mediaId);

        if (!$media) {
            $sessionMessagesManager->recordNotFound();

            return $app->redirect($app['url_generator']->generate('calendarMediaIndex', ['calendarId' => $calendarId, 'page' => 1]));
        }

        $form = $app['form.factory']->createBuilder(FormType::class, $media)
            ->add('id', HiddenType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mediaRepository->deleteCalendarMediaLink($calendarId, $mediaId);
            $sessionMessagesManager->deleted();

            return $app->redirect($app['url_generator']->generate('calendarMediaIndex', ['calendarId' => $calendarId, 'page' => 1]));
        }

        return $app['twig']->render(
            'media/md-delete.html.twig',
            [
                'dataToDelete' => $media,
                'form' => $form->createView(),
                'userId' => $userId,
            ]
        );
    }

    public function deleteMediaAction(Application $app, $userId, $mediaId, Request $request)
    {
        $sessionMessagesManager = new SessionMessagesDataManager($app['session']);
        $mediaRepository = new MediaRepository($app['db']);

        $media = $mediaRepository->findOneById($mediaId);

        if (!$media) {
            $sessionMessagesManager->recordNotFound();

            return $app->redirect($app['url_generator']->generate('userMediaIndex', ['userId' => $userId, 'page' => 1]));
        }

        $form = $app['form.factory']->createBuilder(FormType::class, $media)
            ->add('id', HiddenType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mediaRepository->deleteUserMediaLink($userId, $mediaId);
            $sessionMessagesManager->deleted();

            return $app->redirect($app['url_generator']->generate('userMediaIndex', ['userId' => $userId, 'page' => 1]));
        }

        return $app['twig']->render(
            'media/md-delete.html.twig',
            [
                'dataToDelete' => $media,
                'form' => $form->createView(),
                'userId' => $userId,
            ]
        );
    }

    /**
     *
     * @param Application $app
     *
     * @param Request     $request
     *
     * @param int         $page
     *
     * @return mixed
     */
    public function userMediaIndexAction(Application $app, Request $request, $userId, $page = 1)
    {
        //TODO get id from logged user

        $mediaRepository = new MediaRepository($app['db']);

        $queryParams = ['userId' => $userId, 'page' => $page];
        $paginator = $mediaRepository->getSearchedAndPaginatedRecordsForUser($queryParams);

        $form = $app['form.factory']
            ->createBuilder(SearchType::class)
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $paginator = $mediaRepository->getSearchedAndPaginatedRecordsForUser($queryParams, $form->getData());
        }

        return $app['twig']->render(
            'media/md-user_index.html.twig',
            [
                'form' => $form->createView(),
                'pagerfanta' => $paginator,
                'userId' => $userId,
            ]
        );
    }

    /**
     *
     * @param Application $app
     *
     * @param int         $calendarId
     *
     * @param Request     $request
     *
     * @param int         $page
     *
     * @return mixed
     */
    public function eventMediaIndexAction(Application $app, $calendarId, Request $request, $page = 1)
    {
        //TODO get id from logged user
        //TODO search witch data transformer, nie zrobic wlasne
        //TODO paginator do repozytorium i inne query do niego
        //todo usun jeśli nieużywane
        $userId = 1;

        $mediaRepository = new MediaRepository($app['db']);

        $queryParams = ['userId' => $userId, 'calendarId' => $calendarId, 'page' => $page];
        $paginator = $mediaRepository->getSearchedAndPaginatedRecordsForUserAndCalendar($queryParams);

        $form = $app['form.factory']
            ->createBuilder(SearchType::class)
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $paginator = $mediaRepository
                ->getSearchedAndPaginatedRecordsForUserAndCalendar($queryParams, $form->getData());
        }

        return $app['twig']->render(
            'media/md-calendar_index.html.twig',
            [
                'form' => $form->createView(),
                'pagerfanta' => $paginator,
                'userId' => $userId,
                'calendarId' => $calendarId,
            ]
        );
    }

    /**
     *
     * @param Application $app
     *
     * @param int         $calendarId
     *
     * @param Request     $request
     *
     * @param int         $page
     *
     * @return mixed
     */
    public function calendarMediaIndexAction(Application $app, $calendarId, Request $request, $page = 1)
    {
        //TODO get id from logged user
        //TODO search witch data transformer, nie zrobic wlasne
        //TODO paginator do repozytorium i inne query do niego
        $userId = 1;

        $mediaRepository = new MediaRepository($app['db']);

        $queryParams = [ 'calendarId' => $calendarId, 'page' => $page];
        $paginator = $mediaRepository->getSearchedAndPaginatedRecordsForCalendar($queryParams);

        $form = $app['form.factory']
            ->createBuilder(SearchType::class)
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $paginator = $mediaRepository
                ->getSearchedAndPaginatedRecordsForCalendar($queryParams, $form->getData());
        }

        return $app['twig']->render(
            'media/md-calendar_index.html.twig',
            [
                'form' => $form->createView(),
                'pagerfanta' => $paginator,
                'routeName' => 'calendarMediaIndex',
                'userId' => $userId,
                'calendarId' => $calendarId,
            ]
        );
    }
}
