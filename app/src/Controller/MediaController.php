<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 07.06.18
 * Time: 10:18
 */

namespace Controller;

use Form\MediaType;
use Repositiory\MediaRepository;
use Service\FileUploader;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
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
        $controller->match('/add', [$this, 'addMediaAction'])
            ->method('POST|GET')
            ->assert('userId', '[1-9]\d*')
            ->assert('page', '[1-9]\d*')
            ->bind('addMedia');

        $controller->match('/{mediaId}/edit', [$this, 'editMediaAction'])
            ->method('POST|GET')
            ->bind('editMedia');
        $controller->match('/{mediaId}/delete', [$this, 'editMediaAction'])
            ->method('POST|GET')
            ->bind('deleteMedia');

        $controller->match('/index/page/{page}', [$this, 'userMediaIndexAction'])
            ->assert('page', '[1-9]\d*')
            ->bind('userMediaIndex');

        $controller->match('{calendarId}/index/page/{page}', [$this, 'calendarMediaIndexAction'])
            ->method('POST|GET')
            ->assert('userId', '[1-9]\d*')
            ->assert('page', '[1-9]\d*')
            ->bind('userCalendarMediaIndex');

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
    public function addMediaAction(Application $app, Request $request)
    {
        //TODO get id from logged user
        $userId = 1;

        $media = [];
        $form = $app['form.factory']->createBuilder(MediaType::class, $media)->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $photo  = $form->getData();
            $fileUploader = new FileUploader($app['config.photos_directory']);
            $fileName = $fileUploader->upload($photo['photo']);
            $photo['photo'] = $fileName;
            $mediaRepository = new MediaRepository($app['db']);
            $mediaRepository->save($photo, $userId);
        }

        return $app['twig']->render(
            'media/add.html.twig',
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
     * @param int         $page
     *
     * @param Request     $request
     *
     * @return mixed
     */
    public function userMediaIndexAction(Application $app, $page, Request $request)
    {
        //TODO get id from logged user
        //TODO search witch data transformer, nie zrobic wlasne :(
        //TODO paginator do repozytorium i inne query do niego
        $userId = 1;

        $mediaRepository = new MediaRepository($app['db']);

        $paginator = new MyPaginatorShort(
            $mediaRepository->queryAll(),
            5,
            'm.id',
            $page
        );

        return $app['twig']->render(
            'media/index.html.twig',
            [
                'pagerfanta' => $paginator->pagerfanta,
                'userId' => $userId,
            ]
        );
    }

    /**
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
    public function calendarMediaIndexAction(Application $app, $calendarId, $page, Request $request)
    {
        //TODO get id from logged user
        //TODO search witch data transformer, nie zrobic wlasne
        //TODO paginator do repozytorium i inne query do niego
        $userId = 1;

        $mediaRepository = new MediaRepository($app['db']);

        $paginator = new MyPaginatorShort(
            $mediaRepository->queryAll(),
            5,
            'm.id',
            $page
        );

        return $app['twig']->render(
            'media/index.html.twig',
            [
                'pagerfanta' => $paginator->pagerfanta,
                'userId' => $userId,
                'calenarId' => $calendarId,
            ]
        );
    }
}
