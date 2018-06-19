<?php
/**
 * Created by PhpStorm.
 * User: Kuba
 * Date: 24.04.18
 * Time: 21:45
 */

namespace Controller;

use DataManager\SessionMessagesDataManager;
use Form\DeleteUserType;
use Form\EditUserType;
use Form\RegisterType;
use Form\Search\SearchType;
use Repositiory\UserCaledarRepository;
use Repositiory\UserRepository;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use DataManager\UserDataManager;

/**
 * Class UserController
 */
class UserController implements ControllerProviderInterface
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

        $controller->match('/register', [$this, 'registerAction'])
            ->assert('userId', '[1-9]\d*')
            ->method('POST|GET')
            ->bind('register');

        $controller->match('/{userId}/edit', [$this, 'editAction'])
            ->assert('userId', '[1-9]\d*')
            ->method('POST|GET')
            ->bind('editUser');

        $controller->match('/{userId}/delete', [$this, 'deleteAction'])
            ->assert('userId', '[1-9]\d*')
            ->method('POST|GET')
            ->bind('deleteUser');

        $controller->match('/{userId}/index/page/{page}', [$this, 'userCalendarIndexAction'])
            ->method('POST|GET')
            ->assert('userId', '[1-9]\d*')
            ->assert('page', '[1-9]\d*')
            ->bind('userCalendarIndex');

        $controller->match('/{userId}/settings', [$this, 'settingsAction'])
            ->assert('userId', '[1-9]\d*')
            ->method('POST|GET')
            ->bind('settingsUser');

        return $controller;
    }

    /**
     * Register user
     *
     * @param Application $app
     *
     * @param Request     $request
     *
     * @return mixed
     *
     * @throws \Doctrine\DBAL\ConnectionException
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function registerAction(Application $app, Request $request)
    {
        $sessionMessages = new SessionMessagesDataManager($app['session']);
        $user = [];
        $form = $app['form.factory']
            ->createBuilder(RegisterType::class, $user, ['repository' => new UserRepository($app['db'])])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repository = new UserRepository($app['db']);
            $manager = new UserDataManager($form->getData(), $app['security.encoder.bcrypt']);
            $manager->setUser('ROLE_USER');
            $user = $manager->getUser();

            $repository->save($user, $app['security.encoder.bcrypt']);
            $sessionMessages->registered();

            return $app->redirect($app['url_generator']->generate('auth_login'), 301);
        }


        return $app['twig']->render(
            'user/register.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    /**
     * Edit user
     * @param Application $app
     * @param int         $userId
     *
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function editAction(Application $app, $userId, Request $request)
    {
        //TODO get id from logged user
        //TODO get user_role of logged user
        $token = $app['security.token_storage']->getToken();
        $loggedUserId = $token->getUser()->getId();
        if (!$app['security.authorization_checker']->isGranted('this_user', $userId)) {
            $sessionMessagesManager = new SessionMessagesDataManager($app['session']);
            $sessionMessagesManager->accesDenied();

            return $app->redirect($app['url_generator']->generate('userCalendarIndex', ['userId' => $loggedUserId, 'page' => 1]));
        }

        $userRepository = new UserRepository($app['db']);
        $sessionMessages = new SessionMessagesDataManager($app['session']);

        $user = $userRepository->findOneById($userId);
        if (!$user) {
            $sessionMessages->recordNotFound();

            return $app->redirect($app['url_generator']->generate('settingsUser', ['userId' => $userId]), 301);
        }

        $form = $app['form.factory']
            ->createBuilder(
                EditUserType::class,
                $user,
                [
                'repository' => new UserRepository($app['db']),
                'bcrypt' => $app['security.encoder.bcrypt'],
                ]
            )->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->save($form->getData(), $app['security.encoder.bcrypt']);
            $sessionMessages->changed();

            return $app->redirect($app['url_generator']->generate('settingsUser', ['userId' => $userId]), 301);
        }

        return $app['twig']->render(
            'user/user-edit.html.twig',
            [
                'form' => $form->createView(),
                'userId' => $userId,
            ]
        );
    }

    /**
     * Delete account and its links, NOT REMOVE CALENDARS AND MEDIA
     * @param Application $app
     * @param int         $userId
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function deleteAction(Application $app, $userId, Request $request)
    {
        //todo checker
        $token = $app['security.token_storage']->getToken();
        $loggedUserId = $token->getUser()->getId();
        if (!$app['security.authorization_checker']->isGranted('this_user', $userId)) {
            $sessionMessagesManager = new SessionMessagesDataManager($app['session']);
            $sessionMessagesManager->accesDenied();

            return $app->redirect($app['url_generator']->generate('userCalendarIndex', ['userId' => $loggedUserId, 'page' => 1]));
        }


        $userRepository = new UserRepository($app['db']);
        $sessionMessages = new SessionMessagesDataManager($app['session']);

        $user = $userRepository->findOneById($userId);

        if (!$user) {
            $sessionMessages->recordNotFound();

            return $app->redirect($app['url_generator']->generate('settingsUser', ['userId' => $userId]), 301);
        }

        $form = $app['form.factory']
            ->createBuilder(
                DeleteUserType::class,
                $user,
                [
                    'repository' => new UserRepository($app['db']),
                    'bcrypt' => $app['security.encoder.bcrypt'],
                ]
            )->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userRepository->delete($user['id']);
            $sessionMessages->deleted();

            return $app->redirect($app['url_generator']->generate('register'), 301);
        }

        return $app['twig']->render(
            'user/user-delete.html.twig',
            [
                'form' => $form->createView(),
                'userId' => $loggedUserId,
            ]
        );
    }

    /**
     *
     * @param Application $app
     *
     * @param Int         $userId
     * @param Int         $page
     *
     * @param Request     $request
     *
     * @return mixed
     */
    public function userCalendarIndexAction(Application $app, $userId, $page, Request $request)
    {
        //TODO pierwszy checker
        $token = $app['security.token_storage']->getToken();
        $loggedUserId = $token->getUser()->getId();
        if (!$app['security.authorization_checker']->isGranted('this_user', $userId)) {
            $sessionMessagesManager = new SessionMessagesDataManager($app['session']);
            $sessionMessagesManager->accesDenied();

            return $app->redirect($app['url_generator']->generate('userCalendarIndex', ['userId' => $loggedUserId, 'page' => 1]));
        }

        $userCalendarRepository = new UserCaledarRepository($app['db']);
        $queryParams = ['userId' => $userId, 'page' => $page];
        $paginator = $userCalendarRepository->getSearchPaginatedCalendarsByUserId($queryParams);

        $form = $app['form.factory']
            ->createBuilder(SearchType::class)
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $paginator = $userCalendarRepository->getSearchPaginatedCalendarsByUserId($queryParams, $form->getData());
        }

        return $app['twig']->render(
            'calendar/calendar-index.html.twig',
            [
                'form' => $form->createView(),
                'pagerfanta' => $paginator,
                'routeName' => 'userCalendarIndex',
                'userId' => $loggedUserId,
            ]
        );
    }

    /**
     * User setting options
     * @param Application $app
     *
     * @param int         $userId
     *
     * @return mixed
     */
    public function settingsAction(Application $app, $userId)
    {
        //TODO checker
        $token = $app['security.token_storage']->getToken();
        $loggedUserId = $token->getUser()->getId();

        if (!$app['security.authorization_checker']->isGranted('this_user', $userId)) {
            $sessionMessagesManager = new SessionMessagesDataManager($app['session']);
            $sessionMessagesManager->accesDenied();

            return $app->redirect($app['url_generator']->generate('userCalendarIndex', ['userId' => $loggedUserId, 'page' => 1]));
        }

        return $app['twig']->render(
            'user/user-settings.html.twig',
            [
                'userId' => $loggedUserId,
            ]
        );
    }
}
