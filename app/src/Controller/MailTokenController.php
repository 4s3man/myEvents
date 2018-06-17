<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 15.06.18
 * Time: 11:45
 */

namespace Controller;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class MailTokenController
 */
class MailTokenController implements ControllerProviderInterface
{
    /**
     * Set routing
     *
     * @param Application $app
     *
     * @return mixed|ControllerCollection
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];

        $controller->match('/{token}', [$this, 'signUpTokenAction'])
            ->method('POST|GET')
            ->bind('signUpToken');

        return $controller;
    }

    /**
     * TODO zrobić maile
     * @param Application $app
     *
     * @param int         $token
     *
     * @param Request     $request
     *
     * @return mixed
     */
    public function signUpTokenAction(Application $app, $token, Request $request)
    {
        $s = new \Swift_Message();
        $s->setSubject('i dono')
            ->setFrom('myEvents@mysite.com')
            ->setTo(array('kuba.kulaga.sv7@gmail.com'))
            ->setBody('okok');

        $app['mailer']->send($s);

        return $app['twig']->render(
            'mailToken/mT-signUp.html.twig',
            [

            ]
        );
    }
}
