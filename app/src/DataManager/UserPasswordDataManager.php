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

/**
 * Class UserPasswordManager
 */
class UserPasswordDataManager
{
    /**
     * @var array|null
     */
    private $user = null;

    /**
     * UserPasswordManager constructor.
     * @param array                 $user
     * @param BCryptPasswordEncoder $encoder
     */
    public function __construct($user, BCryptPasswordEncoder $encoder)
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
