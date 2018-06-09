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

/**
 * Class UserPasswordManager
 */
class UserDataManager
{
    /**
     *
     * @var array|null
     */
    private $user = null;

    /**
     *
     * @var array $options
     */
    private $options = ['NORMAL_USER', 'SUPER_USER'];


    /**
     * UserPasswordManager constructor.
     *
     * @param array                 $user
     * @param BCryptPasswordEncoder $encoder
     */
    public function __construct($user, BCryptPasswordEncoder $encoder)
    {
        $user['password'] = $encoder->encodePassword($user['password'], '');
        $this->user = $user;
    }

    /**
     *
     * @return null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Sets user role
     *
     * @param string $user
     */
    public function setUser($user)
    {
        if (!in_array($user, $this->options)) {
            throw new InvalidArgumentException('Invalid option in dataManager setUser function');
        }

        switch ($user) {
            case 'NORMAL_USER':
                $this->user['role'] = 'NORMAL_USER';
                break;
            case 'SUPER_USER':
                $this->user['role'] = 'SUPER_USER';
                break;
        }
    }
}
