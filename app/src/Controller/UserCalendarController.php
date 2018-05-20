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
class UserCalendarController implements ControllerProviderInterface
{
    /**
     * @param Application $app
     *
     * @return mixed|\Silex\ControllerCollection
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];
        $controller->get('/{userId}', [$this, 'userCalendarIndex'])
            ->bind('userCalendars');

        return $controller;
    }

    /**
     * @param Application $app
     *
     * @param int         $calendarId
     *
     * @return mixed
     */
    public function userCalendarIndex(Application $app, $calendarId)
    {
        return $app['twig']->render(
            'settings/index.html.twig',
            [
                'calendarId' => $calendarId,
            ]
        );
    }
}
