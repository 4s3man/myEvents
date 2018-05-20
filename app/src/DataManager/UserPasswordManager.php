<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 20.05.18
 * Time: 18:17
 */

namespace DataManager;


use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use Symfony\Component\Security\Http\Tests\TestFailureHandlerInterface;

class UserPasswordManager
{
    private $user = null;

    public function __construct( Array $user, BCryptPasswordEncoder $encoder )
    {
        $user['password'] = $encoder->encodePassword($user['password'], '');
        $this->user = $user;
    }

    /**
     * @return null
     */
    public function getUser()
    {
        return $this->user;
    }


}