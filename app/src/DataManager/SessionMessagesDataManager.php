<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 23.05.18
 * Time: 22:49
 */

namespace DataManager;

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class SessionMessagesDataManager
 */
class SessionMessagesDataManager
{
    /**
     * @var null|Session
     */
    private $session = null;

    /**
     * SessionMessagesDataManager constructor.
     *
     * @param Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * Display message for added
     */
    public function added()
    {
        $this->success('added');
    }

    /**
     * Display message changed
     */
    public function changed()
    {
        $this->success('changed');
    }

    /**
     * Display sussess registered
     */
    public function registered()
    {
        $this->success('registered');
    }

    public function emailSend()
    {
        $this->success('email_send');
    }

    public function signedUp()
    {
        $this->success('signed_up');
    }

    /**
     * Display success deleted
     */
    public function deleted()
    {
        $this->success('deleted');
    }

    /**
     * Display record not found
     */
    public function recordNotFound()
    {
        $this->error('record_not_found');
    }

    /**
     * Error invalid input
     */
    public function invalidInput()
    {
        $this->error('invalid_input');
    }

    /**
     * Error already linked
     */
    public function alereadyLinked()
    {
        $this->error('already_linked');
    }

    /**
     * Set success message
     *
     * @param string $action message name part
     */
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

    /**
     * Set error message
     *
     * @param string $action message name part
     */
    private function error($action)
    {
        $this->session->getFlashBag()->add(
            'messages',
            [
                'type' => 'danger',
                'message' => 'message.'.$action.'_error',
            ]
        );
    }
}
