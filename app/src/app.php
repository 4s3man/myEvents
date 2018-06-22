<?php

use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\LocaleServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ValidatorServiceProvider;

$app = new Application();
$app->register(new ServiceControllerServiceProvider());
$app->register(new AssetServiceProvider());
$app->register(new TwigServiceProvider());
$app->register(new HttpFragmentServiceProvider());

$app['config.photos_directory'] = __DIR__.'/../web/uploads/photos';
$app['config.download_photos_directory'] = '/uploads/photos';
//TODO nie działają obrazki więc taki fix
$app['config.photos_fix'] = isset($_SERVER['REQUEST_SCHEME']) ?
    $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'].'/../uploads/photos'
    : '/uploads/photos';

$app['twig'] = $app->extend(
    'twig',
    function ($twig, $app) {
        $twig->addGlobal('photos_directory', $app['config.photos_directory']);
        $twig->addGlobal('download_photos_directory', $app['config.download_photos_directory']);
        $twig->addGlobal('probablyBadPhotoFix', $app['config.photos_fix']);

        return $twig;
    }
);

$app->register(new LocaleServiceProvider());
$app->register(
    new TranslationServiceProvider(),
    [
        'locale' => 'pl',
        'locale_fallbacks' => array('en'),
    ]
);
$app->extend(
    'translator',
    function ($translator, $app) {
        $translator->addResource('xliff', __DIR__.'/../translations/messages.en.xlf', 'en', 'messages');
        $translator->addResource('xliff', __DIR__.'/../translations/validators.en.xlf', 'en', 'validators');
        $translator->addResource('xliff', __DIR__.'/../translations/messages.pl.xlf', 'pl', 'messages');
        $translator->addResource('xliff', __DIR__.'/../translations/validators.pl.xlf', 'pl', 'validators');

        return $translator;
    }
);
$app->register(
    new DoctrineServiceProvider(),
    [
        'db.options' => [
            'driver'    => 'pdo_mysql',
            'host'      => 'localhost',
            'dbname'    => '16_kulaga',
            'user'      => '16_kulaga',
            'password'  => 'J7c7t9e5h3',
            'charset'   => 'utf8',
            'driverOptions' => [
                1002 => 'SET NAMES utf8',
            ],
        ],
    ]
);
$app->register(new FormServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new SessionServiceProvider());

$app->register(
    new SecurityServiceProvider(),
    [
        'security.firewalls' => [
            'dev' => [
                'pattern' => '^/(_(profiler|wdt)|css|images|js)/',
                'security' => false,
            ],
            'main' => [
                'pattern' => '^.*$',
                'form' => [
                    'login_path' => 'auth_login',
                    'check_path' => 'auth_login_check',
                    'default_target_path' => 'auth_login',
                    'username_parameter' => 'login_type[login]',
                    'password_parameter' => 'login_type[password]',
                ],
                'anonymous' => true,
                'logout' => [
                    'logout_path' => 'auth_logout',
                    'target_url' => 'auth_login',
                ],
                'users' => function () use ($app) {
                    return new \Provider\MyEventsUserProvider($app['db']);
                },
            ],
        ],
        'security.access_rules' => [
            ['^/(auth|user/registe|calendar/[1-9]\d*/\d.*|event/[1-9]\d*/([1-9]\d*/sho|index/)|token/).+$', 'IS_AUTHENTICATED_ANONYMOUSLY'],
            ['^/.+$', 'IS_AUTHENTICATED_FULLY'],
        ],
        'security.role_hierarchy' => [
            'ROLE_ADMIN' => ['ROLE_USER'],
        ],
    ]
);

$app['security.voters'] = $app->extend(
    'security.voters',
    function ($voters) use ($app) {
        $voters[] = new \Security\CalendarVoter();
        $voters[] = new \Security\UserVoter();

        return $voters;
    }
);

return $app;
