<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 23.05.18
 * Time: 22:49
 */

namespace DataManager;

use Symfony\Component\HttpFoundation\Session\Session;

class SessionMessagesDataManager
{
    /**
     * @var null|Session
     */
    private $session = null;

    /**
     * SessionMessagesDataManager constructor.
     */
    public function __construct(Session $session )
    {
        $this->session = $session;
    }

    public function added()
    {
        $this->success('added');
    }

    public function changed()
    {
        $this->success('changed');
    }

    public function registered()
    {
        $this->success('registered');
    }

    public function deleted()
    {
        $this->success('deleted');
    }

    public function recordNotFound()
    {
        $this->error('record_not_found');
    }

    public function invalidInput()
    {
        $this->error('invalid_input');
    }

    private function success($action)
    {
        $this->session->getFlashBag()->add(
            'messages',
            [
                'type' => 'success',
                'message' => 'message.'.$action.'_succesfully',
            ]
        );
    }

    private function error( $record )
    {
        $this->session->getFlashBag()->add(
            'messages',
            [
                'type' => 'success',
                'message' => 'message.'.$record.'_error',
            ]
        );
    }

}