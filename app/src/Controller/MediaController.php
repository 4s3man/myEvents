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

        $controller->match('/{userId}/add', [$this, 'addUserMedia'])
            ->method('POST|GET')
            ->assert('userId', '[1-9]\d*')
            ->assert('page', '[1-9]\d*')
            ->bind('userAddMedia');

        $controller->match('/calendar/{calendarId}/add', [$this, 'calendarAddMediaAction'])
            ->method('POST|GET')
            ->assert('calendarId', '[1-9]\d*')
            ->assert('page', '[1-9]\d*')
            ->bind('calendarAddMedia');

        $controller->match('/user/{userId}/{mediaId}/edit', [$this, 'editUserMediaAction'])
            ->assert('userId', '[1-9]\d*')
            ->assert('mediaId', '[1-9]\d*')
            ->method('POST|GET')
            ->bind('editUserMedia');

        $controller->match('/user/{userId}/{mediaId}/delete', [$this, 'deleteUserMediaAction'])
            ->assert('userId', '[1-9]\d*')
            ->assert('mediaId', '[1-9]\d*')
            ->method('POST|GET')
            ->bind('deleteMedia');

        $controller->match('/user/{userId}/index/page/{page}', [$this, 'userMediaIndexAction'])
            ->assert('userId', '[1-9]\d*')
            ->assert('page', '[1-9]\d*')
            ->bind('userMediaIndex');

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
     * Adds media to specified user
     * @param Application $app
     *
     * @param int         $userId
     *
     * @param Request     $request
     *
     * @return mixed
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function addUserMedia(Application $app, $userId, Request $request)
    {
        $token = $app['security.token_storage']->getToken();
        $loggedUserId = $token->getUser()->getId();
        if (!$app['security.authorization_checker']->isGranted('this_user', $userId)) {
            return $app->redirect($app['url_generator']->generate('userCalendarIndex', ['userId' => $loggedUserId, 'page' => 1]));
        }

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
        $token = $app['security.token_storage']->getToken();
        $loggedUserId = $token->getUser()->getId();
        $sessionMessagesManager = new SessionMessagesDataManager($app['session']);

        if (!$app['security.authorization_checker']->isGranted('calendar_any_user', $calendarId)) {
            return $app->redirect($app['url_generator']->generate('userCalendarIndex', ['userId' => $loggedUserId, 'page' => 1]));
        }

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
                'userId' => $loggedUserId,
                'calendarId' => $calendarId,
            ]
        );
    }

    /**
     * Edit user media
     * @param Application $app
     *
     * @param int         $userId
     * @param int         $mediaId
     *
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function editUserMediaAction(Application $app, $userId, $mediaId, Request $request)
    {
        //todo checker
        $token = $app['security.token_storage']->getToken();
        $loggedUserId = $token->getUser()->getId();
        if (!$app['security.authorization_checker']->isGranted('this_user', $userId)) {
            return $app->redirect($app['url_generator']->generate('userCalendarIndex', ['userId' => $loggedUserId, 'page' => 1]));
        }

        $sessionMessagesManager = new SessionMessagesDataManager($app['session']);
        $mediaRepository = new MediaRepository($app['db']);

        $media = $mediaRepository->findOneById($mediaId);

        if (!$media) {
            $sessionMessagesManager->recordNotFound();

            return $app->redirect($app['url_generator']->generate('userMediaIndex', ['userId' => $loggedUserId, 'page' => 1]));
        }

        $form = $app['form.factory']->createBuilder(TitleType::class, $media)->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mediaRepository->saveToUser($form->getData(), $userId);
            $sessionMessagesManager->changed();

            return $app->redirect($app['url_generator']->generate('userMediaIndex', ['userId' => $loggedUserId, 'page' => 1]));
        }

        return $app['twig']->render(
            'media/md-edit.html.twig',
            [
                'form' => $form->createView(),
                'userId' => $loggedUserId,
            ]
        );
    }

    /**
     * Edit calendar media
     * @param Application $app
     *
     * @param int         $calendarId
     * @param int         $mediaId
     *
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Doctrine\DBAL\ConnectionException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function editCalendarMediaAction(Application $app, $calendarId, $mediaId, Request $request)
    {
        $token = $app['security.token_storage']->getToken();
        $loggedUserId = $token->getUser()->getId();
        $sessionMessagesManager = new SessionMessagesDataManager($app['session']);

        if (!$app['security.authorization_checker']->isGranted('calendar_any_user', $calendarId)) {
            return $app->redirect($app['url_generator']->generate('userCalendarIndex', ['userId' => $loggedUserId, 'page' => 1]));
        }

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
                'userId' => $loggedUserId,
                'calendarId' => $calendarId,
            ]
        );
    }

    /**
     * Delete calendar media
     * @param Application $app
     *
     * @param int         $calendarId
     * @param int         $mediaId
     *
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteCalendarMediaAction(Application $app, $calendarId, $mediaId, Request $request)
    {
        $token = $app['security.token_storage']->getToken();
        $loggedUserId = $token->getUser()->getId();
        $sessionMessagesManager = new SessionMessagesDataManager($app['session']);

        if (!$app['security.authorization_checker']->isGranted('calendar_any_user', $calendarId)) {
            return $app->redirect($app['url_generator']->generate('userCalendarIndex', ['userId' => $loggedUserId, 'page' => 1]));
        }

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
                'userId' => $loggedUserId,
            ]
        );
    }

    /**
     * Delete user media
     * @param Application $app
     *
     * @param int         $userId
     * @param int         $mediaId
     *
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteUserMediaAction(Application $app, $userId, $mediaId, Request $request)
    {
        $token = $app['security.token_storage']->getToken();
        $loggedUserId = $token->getUser()->getId();
        if (!$app['security.authorization_checker']->isGranted('this_user', $userId)) {
            return $app->redirect($app['url_generator']->generate('userCalendarIndex', ['userId' => $loggedUserId, 'page' => 1]));
        }

        $sessionMessagesManager = new SessionMessagesDataManager($app['session']);
        $mediaRepository = new MediaRepository($app['db']);

        $media = $mediaRepository->findOneById($mediaId);

        if (!$media) {
            $sessionMessagesManager->recordNotFound();

            return $app->redirect($app['url_generator']->generate('userMediaIndex', ['userId' => $loggedUserId, 'page' => 1]));
        }

        $form = $app['form.factory']->createBuilder(FormType::class, $media)
            ->add('id', HiddenType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mediaRepository->deleteUserMediaLink($userId, $mediaId);
            $sessionMessagesManager->deleted();

            return $app->redirect($app['url_generator']->generate('userMediaIndex', ['userId' => $loggedUserId, 'page' => 1]));
        }

        return $app['twig']->render(
            'media/md-delete.html.twig',
            [
                'dataToDelete' => $media,
                'form' => $form->createView(),
                'userId' => $loggedUserId,
            ]
        );
    }

    /**
     *
     * @param Application $app
     *
     * @param Request     $request
     *
     * @param int         $userId
     * @param int         $page
     *
     * @return mixed
     */
    public function userMediaIndexAction(Application $app, Request $request, $userId, $page = 1)
    {
        $token = $app['security.token_storage']->getToken();
        $loggedUserId = $token->getUser()->getId();
        if (!$app['security.authorization_checker']->isGranted('this_user', $userId)) {
            return $app->redirect($app['url_generator']->generate('userCalendarIndex', ['userId' => $loggedUserId, 'page' => 1]));
        }

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
                'userId' => $loggedUserId,
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
        $token = $app['security.token_storage']->getToken();
        $loggedUserId = $token->getUser()->getId();

        if (!$app['security.authorization_checker']->isGranted('calendar_any_user', $calendarId)) {
            return $app->redirect($app['url_generator']->generate('userCalendarIndex', ['userId' => $loggedUserId, 'page' => 1]));
        }

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
                'userId' => $loggedUserId,
                'calendarId' => $calendarId,
            ]
        );
    }
}
