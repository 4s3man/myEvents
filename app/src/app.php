<?php

use Silex\Application;
use Silex\Provider\AssetServiceProvider;
use Silex\Provider\DoctrineServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\HttpFragmentServiceProvider;
use Silex\Provider\LocaleServiceProvider;
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
$app['twig'] = $app->extend(
    'twig', function ($twig, $app) {
        // add custom globals, filters, tags, ...

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
    'translator', function ($translator, $app) {
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

return $app;
