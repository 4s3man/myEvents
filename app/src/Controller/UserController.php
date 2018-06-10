<?php
/**
 * Created by PhpStorm.
 * User: Kuba
 * Date: 24.04.18
 * Time: 21:45
 */

namespace Controller;

use DataManager\SessionMessagesDataManager;
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

            $repository->save($user);
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
}
