<?php
/**
 * Created by PhpStorm.
 * User: Kuba
 * Date: 24.04.18
 * Time: 21:45
 */

namespace Controller;

use Form\RegisterType;
use Repositiory\UserRepository;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;
use Utils\MyPaginatorShort;

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

        $controller->get('/', [$this, 'loggedRedirect']);
        $controller->get('/login', [ $this, 'loginAction'])
            ->method('POST|GET')
            ->bind('login');
        $controller->get('/register', [$this, 'registerAction'])
            ->method('POST|GET')
            ->bind('register');
        $controller->get('/index', [$this, 'indexAction'])
            ->bind('userIndex');

        return $controller;
    }

    /**
     * TODO: redirect depending on Session logged
     *
     * @param Application $app
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function loggedRedirect(Application $app)
    {
        return $app->redirect($app['url_generator']->generate('register'));
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
     * @param Application $app
     *
     * @param Request     $request
     *
     * @return mixed
     */
    public function registerAction(Application $app, Request $request)
    {
        $tag = [];
        $form = $app['form.factory']->createBuilder(RegisterType::class, $tag, ['repository' => new UserRepository($app['db'])])->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $repository = new userRepository($app['db']);
            $tag = $form->getData();
            $repository->save($tag);
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


    public function indexAction(Application $app)
    {
        $userRepository = new UserRepository($app['db']);
        $paginator = new MyPaginatorShort(
            $app,
            $userRepository->queryAll(),
            5,
            'userIndex'
            );

        return $app['twig']->render(
            'user/index.html.twig',
            [
                'paginator' => $paginator->paginator,
                'data' => $paginator->data,
            ]
        );
    }
}
