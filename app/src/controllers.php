<?php

use Controller\AuthController;
use Controller\EventController;
use Controller\MediaController;
use Controller\SettingsControler;
use Controller\UserController;
use Controller\CalendarUserController;
use Controller\CalendarController;
use Controller\MailTokenController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

//Request::setTrustedProxies(array('127.0.0.1'));

$app->get(
    '/',
    function () use ($app) {
        return $app->redirect($app['url_generator']->generate('register'), 301);
    }
)
->bind('homepage');

$app->error(
    function (\Exception $e, Request $request, $code) use ($app) {
        if ($app['debug']) {
            return;
        }

        // 404.html, or 40x.html, or 4xx.html, or error.html
        $templates = array(
        'errors/'.$code.'.html.twig',
        'errors/'.substr($code, 0, 2).'x.html.twig',
        'errors/'.substr($code, 0, 1).'xx.html.twig',
        'errors/default.html.twig',
        );

        return new Response($app['twig']->resolveTemplate($templates)->render(array('code' => $code)), $code);
    }
);

$app->mount('/user', new UserController());
$app->mount('/calendar', new CalendarController());
$app->mount('/event', new EventController());
$app->mount('/calendarUser', new CalendarUserController());
$app->mount('/auth', new AuthController());
$app->mount('/media', new MediaController());
$app->mount('/token', new MailTokenController());