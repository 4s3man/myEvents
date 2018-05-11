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
use Utils\MyPaginatorShort;
use Pagerfanta\View\TwitterBootstrap4View;

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
        $controller->get('/login', [ $this, 'loginAction'])
            ->method('POST|GET')
            ->bind('login');
        $controller->get('/register', [$this, 'registerAction'])
            ->method('POST|GET')
            ->bind('register');
        $controller->get('{userId}/index/page/{page}', [$this, 'indexAction'])
            ->method('POST|GET')
            ->bind('userIndex');
        $controller->get('{userId}/add', [$this, 'addAction'])
            ->bind('userAdd');

        return $controller;
    }

    /**
     * TODO: redirect depending on Session logged
     *
     * @param Application $app
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function loggedRedirectAction(Application $app)
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

    /**
     * List users and available actions
     *
     * @param Application $app
     *
     * @param int         $userId
     * @param int         $page
     *
     * @return mixed
     */
    public function indexAction(Application $app, $userId, $page = 1)
    {
        $userRepository = new UserRepository($app['db']);

        $paginator = new MyPaginatorShort($userRepository->queryAll(), 5, $page);
        $pagerfanta = $paginator->pagerfanta;
        $view = $this->makeView($app, $pagerfanta, $userId, $page);

        return $app['twig']->render(
            'user/index.html.twig',
            [
                'userId' => $userId,
                'paginator' => $view,
                'users' => $pagerfanta->getCurrentPageResults(),
            ]
        );
    }

    /**
     * Add user to this calendar associated witch userId
     *
     * @param Application $app
     * @param int         $userId
     *
     * @return mixed
     */
    public function addAction(Application $app, $userId)
    {
        return $app['twig']->render(
            'user/add.html.twig',
            [
                'userId' => $userId,
            ]
        );
    }

    /**
     * Make Pagerfanta view html
     *
     * @param Application $app
     * @param Pagerfanta  $pagerfanta
     *
     * @param int         $userId
     * @param int         $page
     *
     * @return String
     */
    private function makeView(Application $app, $pagerfanta, $userId, $page)
    {
        $view = new TwitterBootstrap4View();
        $routeGenerator = function ($page) use ($app, $userId, $page) {

            return $app['url_generator']->generate('userIndex', ['userId' => $userId, 'page' => $page]);
        };
        $options = array(
            'prev_message' => '&larr;'.$app['translator']->trans('paginator.prev'),
            'next_message' => $app['translator']->trans('paginator.next').'&rarr;',
        );

        return $view->render($pagerfanta, $routeGenerator, $options);
    }
}
