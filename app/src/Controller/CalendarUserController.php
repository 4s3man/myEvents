<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 11.05.18
 * Time: 16:55
 */

namespace Controller;

use DataManager\SessionMessagesDataManager;
use Form\LinkUserCalendarType;
use Form\Search\UserSearchType;
use Form\UserRoleType;
use Repositiory\UserCaledarRepository;
use Repositiory\UserRepository;
use Search\Criteria\TypeCriteria;
use Search\CriteriaBuilder\TypeCriteriaBuilder;
use Silex\Api\ControllerProviderInterface;
use Silex\Application;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class UserCalendarController
 */
class CalendarUserController implements ControllerProviderInterface
{
    /**
     *
     * @param Application $app
     *
     * @return mixed|\Silex\ControllerCollection
     */
    public function connect(Application $app)
    {
        $controller = $app['controllers_factory'];

        $controller->match('{calendarId}/index/page/{page}', [$this, 'calendarUserIndexAction'])
            ->method('POST|GET')
            ->assert('calendarId', '[1-9]\d*')
            ->assert('page', '[1-9]\d*')
            ->bind('userIndex');

        $controller->match('{calendarId}/addUser', [$this, 'userAddAction'])
            ->method('POST|GET')
            ->assert('calendarId', '[1-9]\d*')
            ->bind('userAdd');

        $controller->match('/{calendarId}/{userCalendarId}/editUser', [$this, 'userEditAction'])
            ->method('POST|GET')
            ->assert('userCalendarId', '[1-9]\d*')
            ->bind('userEdit');

        $controller->match('/{calendarId}/{userCalendarId}/deleteUser', [$this, 'userDeleteAction'])
            ->method('POST|GET')
            ->assert('userCalendarId', '[1-9]\d*')
            ->bind('userDelete');

        return $controller;
    }

    /**
     * List users and available actions
     *
     * @param Application $app
     *
     * @param int         $calendarId
     *
     * @param Request     $request
     *
     * @param int         $page
     *
     * @return mixed
     */
    public function calendarUserIndexAction(Application $app, $calendarId, Request $request, $page = 1)
    {
        $token = $app['security.token_storage']->getToken();
        $loggedUserId = $token->getUser()->getId();

        if (!$app['security.authorization_checker']->isGranted('calendar_any_user', $calendarId)) {
            return $app->redirect($app['url_generator']->generate('userCalendarIndex', ['userId' => $loggedUserId, 'page' => 1]));
        }

        $userCalendarRepository = new UserCaledarRepository($app['db']);

        $queryParams = ['calendarId' => $calendarId, 'page' => $page];

        $form = $app['form.factory']
            ->createBuilder(UserSearchType::class)
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $paginator = $userCalendarRepository->getSearchPaginatedUsersByCalendarId($queryParams, $form->getData());
        } else {
            $paginator = $userCalendarRepository->getSearchPaginatedUsersByCalendarId($queryParams);
        }


        return $app['twig']->render(
            'calendarUser/cU-index.html.twig',
            [
                'form' => $form->createView(),
                'calendarId' => $calendarId,
                'pagerfanta' => $paginator,
                'routeName' => 'userIndex',
                'userId' => $loggedUserId,
            ]
        );
    }

    /**
     * Add user to this calendar associated witch calendarId
     *
     * @param Application $app
     *
     * @param int         $calendarId
     *
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function userAddAction(Application $app, $calendarId, Request $request)
    {
        $token = $app['security.token_storage']->getToken();
        $loggedUserId = $token->getUser()->getId();

        if (!$app['security.authorization_checker']->isGranted('calendar_admin', $calendarId)) {
            return $app->redirect($app['url_generator']->generate('userCalendarIndex', ['userId' => $loggedUserId, 'page' => 1]));
        }

        $sessionMessagesDataManager = new SessionMessagesDataManager($app['session']);
        $userRepository = new UserRepository($app['db']);
        $userCalendar = [];
        $form = $app['form.factory']
            ->createBuilder(LinkUserCalendarType::class, $userCalendar, ['repository' => $userRepository])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userCalendarRepository = new UserCaledarRepository($app['db']);
            $userCalendar = $form->getData();
            $user = $userRepository->findOneByEmail($userCalendar['email']);

            if ($userCalendarRepository->isLinked($user['id'], $calendarId)) {
                $sessionMessagesDataManager->alereadyLinked();
            } else {
                $userCalendarRepository->linkUserToCalendar(
                    $user['id'],
                    $userCalendar['user_role'],
                    $calendarId
                );
                $sessionMessagesDataManager->added();

                return $app->redirect(
                    $app['url_generator']->generate(
                        'userIndex',
                        [
                            'calendarId' => $calendarId,
                            'page' => 1,
                        ]
                    ),
                    301
                );
            }
        }

        return $app['twig']->render(
            'calendarUser/cU-add.html.twig',
            [
                'form' => $form->createView(),
                'calendarId' => $calendarId,
                'userId' => $loggedUserId,
            ]
        );
    }

    /**
     * Edit user role linked to calnedar
     *
     * @param Application $app
     *
     * @param int         $calendarId
     * @param int         $userCalendarId
     *
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function userEditAction(Application $app, $calendarId, $userCalendarId, Request $request)
    {
        $token = $app['security.token_storage']->getToken();
        $loggedUserId = $token->getUser()->getId();

        if (!$app['security.authorization_checker']->isGranted('calendar_admin', $calendarId)) {
            return $app->redirect($app['url_generator']->generate('userCalendarIndex', ['userId' => $loggedUserId, 'page' => 1]));
        }

        $userCalendarRepository = new UserCaledarRepository($app['db']);
        $sessionDataManager = new SessionMessagesDataManager($app['session']);

        $userType = $userCalendarRepository->findOneById($userCalendarId);
        $form = $app['form.factory']
            ->createBuilder(UserRoleType::class, $userType)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userCalendarRepository->updateUserRoleFoundById($userCalendarId, $form->getData());
            $sessionDataManager->changed();

            return $app->redirect(
                $app['url_generator']->generate(
                    'userIndex',
                    [
                        'calendarId' => $calendarId,
                        'page' => 1,
                    ]
                ),
                301
            );
        }

        return $app['twig']->render(
            'calendarUser/cU-edit.html.twig',
            [
                'form' => $form->createView(),
                'userCalendarId' => $userCalendarId,
                'calendarId' => $calendarId,
                'userId' => $loggedUserId,
            ]
        );
    }

    /**
     * Delete user linked to calnedar
     *
     * @param Application $app
     *
     * @param int         $calendarId
     * @param int         $userCalendarId
     *
     * @param Request     $request
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function userDeleteAction(Application $app, $calendarId, $userCalendarId, Request $request)
    {
        $token = $app['security.token_storage']->getToken();
        $loggedUserId = $token->getUser()->getId();

        if (!$app['security.authorization_checker']->isGranted('calendar_admin', $calendarId)) {
            return $app->redirect($app['url_generator']->generate('userCalendarIndex', ['userId' => $loggedUserId, 'page' => 1]));
        }

        $userCalendarRepository = new UserCaledarRepository($app['db']);
        $userCalendar = $userCalendarRepository->findLinkedUserById($userCalendarId);
        $sessionDataManager = new SessionMessagesDataManager($app['session']);

        if (!$userCalendar) {
            $sessionDataManager->recordNotFound();

            return $app->redirect(
                $app['url_generator']->generate(
                    'userIndex',
                    [
                        'calendarId' => $calendarId,
                        'page' => 1,
                    ]
                ),
                301
            );
        }
        $form = $app['form.factory']
            ->createBuilder(FormType::class, $userCalendar)
            ->add('id', HiddenType::class)
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $form->getData();
            $userCalendarRepository->deleteLink($userCalendarId);
            $sessionDataManager->deleted();

            return $app->redirect(
                $app['url_generator']->generate(
                    'userIndex',
                    [
                        'calendarId' => $calendarId,
                        'page' => 1,
                    ]
                ),
                301
            );
        }

        return $app['twig']->render(
            'calendarUser/cU-delete.html.twig',
            [
                'form' => $form->createView(),
                'userCalendarId' => $userCalendarId,
                'dataToDelete' => $userCalendar,
                'calendarId' => $calendarId,
                'userId' => $loggedUserId,
            ]
        );
    }
}
