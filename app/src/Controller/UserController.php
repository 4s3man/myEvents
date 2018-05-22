<?php
/**
 * Created by PhpStorm.
 * User: Kuba
 * Date: 24.04.18
 * Time: 21:45
 */

namespace Controller;

use Form\RegisterType;
use Pagerfanta\Pagerfanta;
use Repositiory\UserRepository;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use DataManager\UserPasswordManager;

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

        $controller->get('/', [$this, 'loggedRedirectAction']);
        $controller->get('/login', [$this, 'loginAction'])
            ->method('POST|GET')
            ->bind('login');
        $controller->get('/register', [$this, 'registerAction'])
            ->method('POST|GET')
            ->bind('register');

        return $controller;
    }

    /**
     * @param Application $app
     *
     * @return mixed
     */
    public function loginAction(Application $app)
    {
        return $app['twig']->render(
            'user/login.html.twig',
            [

            ]
        );
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
        $user = [];
        $form = $app['form.factory']
            ->createBuilder(RegisterType::class, $user, ['repository' => new UserRepository($app['db'])])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repository = new UserRepository($app['db']);
            $manager = new UserPasswordManager($form->getData(), $app['security.encoder.bcrypt']);
            $user = $manager->getUser();

            //TODO wyrzucic do osobnego obiektu
            $repository->save($user);

            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.registered_succes',
                ]
            );
        }

        return $app['twig']->render(
            'user/register.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }
}
