<?php /** @noinspection PhpParamsInspection */

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
                'type' => 'error',
                'message' => 'message.'.$action.'_error',
            ]
        );
    }
}
