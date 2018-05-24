<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 20.05.18
 * Time: 18:17
 */

namespace DataManager;

use Pagerfanta\Exception\InvalidArgumentException;
use Symfony\Component\Security\Core\Encoder\BCryptPasswordEncoder;
use Symfony\Component\Security\Http\Tests\TestFailureHandlerInterface;

/**
 * Class UserPasswordManager
 */
class UserDataManager
{
    /**
     * @var array|null
     */
    private $user = null;

    /**
     * @var array $options
     */
    private $options = ['normalUser', 'superUser'];


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

    public function setUser(string $user)
    {
        if(!in_array($user, $this->options)){
            throw new InvalidArgumentException('Invalid option in dataManager setUser function');
        }

        switch ($user){
            case 'normalUser':
                $this->user['role'] = 'normalUser';
                break;
            case 'superUser':
                $this->user['role'] = 'superUser';
                break;
        }

    }
}
