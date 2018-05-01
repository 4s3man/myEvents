<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 24.04.18
 * Time: 21:45
 */

namespace Model;

use Form\TagType;
use Repositiory\userRepository;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;

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
    // USUŃ
    //        $controller->get('/{stuff}', [ $this, 'passArg'])
    //            ->assert('stuff', '^((?!add).)*$')
    //            ->bind('yesstuff');


        $controller =$app['controllers_factory'];
        $controller->get('/login', [ $this, 'loginAction'])
            ->method('POST|GET')
            ->bind('login');
        $controller->get('/register', [$this, 'registerAction'])
            ->method('POST|GET')
            ->bind('register');

        return $controller;
    }

    public function loginAction(Application $app, Request $request)
    {
        return $app['twig']->render(
            'user/login.html.twig',
            [

            ]
        );
    }

    public function registerAction(Application $app, Request $request)
    {
        $tag = [];
        $form = $app['form.factory']->createBuilder(TagType::class, $tag)->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $repository = new userRepository($app['db']);
            $tag = $form->getData();
            $repository->save($tag);
            $app['session']->getFlashBag()->add(
                'messages',
                [
                    'type' => 'success',
                    'message' => 'message.element_successfully_added',
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
