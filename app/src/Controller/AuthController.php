<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 25.05.18
 * Time: 13:06
 */

namespace Controller;

use Form\LoginType;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class AuthController
 * used for authentication
 */
class AuthController implements ControllerProviderInterface
{
    /**
     *
     * @param Application $app
     *
     * @return mixed|ControllerCollection
     */
    public function connect(Application $app)
    {
        $connect = $app['controllers_factory'];
        $connect->match('/login', [$this, 'loginAction'])
            ->method('POST|GET')
            ->bind('auth_login');
        $connect->match('/logout', [$this, 'logoutAction'])
            ->method('POST|GET')
            ->bind('logout');

        return $connect;
    }

    /**
     *
     * @param Application $app
     * @param Request     $request
     *
     * @return mixed
     */
    public function loginAction(Application $app, Request $request)
    {
        $user = ['login' => $app['session']->get('_security.last_username')];
        $form = $app['form.factory']->createBuilder(LoginType::class, $user)->getForm();

        return $app['twig']->render(
            'auth/login.html.twig',
            [
                'form' => $form->createView(),
                'error' => $app['security.last_error']($request),
            ]
        );
    }
}
