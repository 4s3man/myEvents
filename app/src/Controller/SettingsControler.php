<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 11.05.18
 * Time: 16:55
 */

namespace Controller;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;

/**
 * Class SettingsControler
 */
class SettingsControler implements ControllerProviderInterface
{
    /**
     * @param Application $app
     *
     * @return mixed|\Silex\ControllerCollection
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->get('/{userId}', [$this, 'settingsIndex'])
            ->bind('settingsIndex');

        return $controller;
    }

    /**
     * @param Application $app
     *
     * @param int         $userId
     *
     * @return mixed
     */
    public function settingsIndex(Application $app, $userId)
    {
        return $app['twig']->render(
            'settings/index.html.twig',
            [
                'userId' => $userId,
            ]
        );
    }
}
