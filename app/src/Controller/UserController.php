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
            ->method('POST|GET')
            ->bind('register');

        $controller->match('/edit', [$this, 'editAction'])
            ->method('POST|GET')
            ->bind('editUser');

        $controller->match('/delete', [$this, 'deleteAction'])
            ->method('POST|GET')
            ->bind('deleteUser');

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
            $manager->setUser('NORMAL_USER');
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

    public function editAction(Application $app, Request $request)
    {
        //TODO get id from logged user
        $userId = 4;
        //TODO get user_role of logged user
        $userRole = 'NORMAL_USER';

        $userRepository = new UserRepository($app['db']);
        $sessionMessages = new SessionMessagesDataManager($app['session']);

        $user = $userRepository->findOneById($userId);
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

//            return $app->redirect($app['url_generator']->generate('auth_login'), 301);
        }

        return $app['twig']->render(
            'user/register.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    public function deleteAction(Application $app, Request $request)
    {
        //todo get id from logged user
        $userId = 4;

        $userRepository = new UserRepository($app['db']);
        $sessionMessages = new SessionMessagesDataManager($app['session']);

        $user = $userRepository->findOneById($userId);
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
            ]
        );
    }
}
