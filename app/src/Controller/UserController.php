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
    const MAIN_ADMIN = 1;
    const CALENDAR_ADMIN = 2;
    const CALENDAR_USER = 3;

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
        $controller->get('{calendarId}/index/page/{page}', [$this, 'indexAction'])
            ->method('POST|GET')
            ->bind('userIndex');
        $controller->get('{calendarId}/add', [$this, 'addAction'])
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
            $repository = new userRepository($app['db']);
            $user = $form->getData();

            $user['password'] = $app['security.encoder.bcrypt']->encodePassword($user['password'], '');
            $user['user_role_id'] = self::CALENDAR_ADMIN;
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

    /**
     * List users and available actions
     *
     * @param Application $app
     *
     * @param int         $calendarId
     * @param int         $page
     *
     * @return mixed
     */
    public function indexAction(Application $app, $calendarId, $page = 1)
    {
        $userRepository = new UserRepository($app['db']);

        $paginator = new MyPaginatorShort($userRepository->queryAll(), 5, $page);
        $pagerfanta = $paginator->pagerfanta;
        $view = $this->makeView($app, $pagerfanta, $calendarId, $page);

        return $app['twig']->render(
            'user/index.html.twig',
            [
                'calendarId' => $calendarId,
                'paginator' => $view,
                'users' => $pagerfanta->getCurrentPageResults(),
            ]
        );
    }

    /**
     * Add user to this calendar associated witch calendarId
     *
     * @param Application $app
     * @param int         $calendarId
     *
     * @return mixed
     */
    public function addAction(Application $app, $calendarId)
    {
        return $app['twig']->render(
            'user/add.html.twig',
            [
                'calendarId' => $calendarId,
            ]
        );
    }

    /**
     * Make Pagerfanta view html
     *
     * @param Application $app
     * @param Pagerfanta  $pagerfanta
     *
     * @param int         $calendarId
     * @param int         $page
     *
     * @return String
     */
    private function makeView(Application $app, $pagerfanta, $calendarId, $page)
    {
        $view = new TwitterBootstrap4View();
        $routeGenerator = function ($page) use ($app, $calendarId, $page) {

            return $app['url_generator']->generate('userIndex', ['calendarId' => $calendarId, 'page' => $page]);
        };
        $options = array(
            'prev_message' => '&larr;'.$app['translator']->trans('paginator.prev'),
            'next_message' => $app['translator']->trans('paginator.next').'&rarr;',
        );

        return $view->render($pagerfanta, $routeGenerator, $options);
    }
}
