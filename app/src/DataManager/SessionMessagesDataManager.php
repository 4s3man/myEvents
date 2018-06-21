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
     *
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
     * Add flash message messsage.added_success
     */
    public function added()
    {
        $this->success('added');
    }

    /**
     * Add flash message messsage.changed_success
     */
    public function changed()
    {
        $this->success('changed');
    }

    /**
     * Add flash message messsage.registered_success
     */
    public function registered()
    {
        $this->success('registered');
    }

    /**
     * Add flash message messsage.mail_send_success
     */
    public function emailSend()
    {
        $this->success('email_send');
    }

    /**
     * Add flash message message.signed_up_success
     */
    public function signedUp()
    {
        $this->success('signed_up');
    }

    /**
     * Display message.deleted_success
     */
    public function deleted()
    {
        $this->success('deleted');
    }

    /**
     * Add flash message message.record_not_found_error
     */
    public function recordNotFound()
    {
        $this->error('record_not_found');
    }

    /**
     * Add flash message message.invelid_input_error
     */
    public function invalidInput()
    {
        $this->error('invalid_input');
    }

    /**
     * Add flash message message.already_linked_error
     */
    public function alereadyLinked()
    {
        $this->error('already_linked');
    }

    /**
     * Add flash message message.acces_denied_error
     */
    public function accesDenied()
    {
        $this->error('acces_denied');
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
