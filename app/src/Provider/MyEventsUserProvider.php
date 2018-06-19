<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 25.05.18
 * Time: 12:44
 */
namespace Provider;

use Doctrine\DBAL\Connection;
use Repositiory\UserRepository;
use Security\Core\User\MyEventsUser;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * Class MyEventsUser
 */
class MyEventsUserProvider implements UserProviderInterface
{
    /**
     *
     * @var Connection|null
     */
    protected $db = null;

    /**
     * MyEventsUser constructor.
     *
     * @param Connection $db
     */
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    /**
     * Return user
     *
     * @param string $login
     *
     * @return User|UserInterface
     */
    public function loadUserByUsername($login)
    {
        $userRepository = new UserRepository($this->db);
        $user = $userRepository->loadUserByLogin($login);

        return new MyEventsUser(
            $user['id'],
            $user['login'],
            $user['password'],
            $user['roles'],
            $user['userCalendars'],
            true,
            true,
            true,
            true
        );
    }

    /**
     * Refresh user
     *
     * @param UserInterface $user
     *
     * @return User|UserInterface
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof UserInterface) {
            throw new UnsupportedUserException(
                sprintf(
                    'Instances of %s are not supported',
                    get_class($user)
                )
            );
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * Check if class is supported
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === 'Security\Core\MyEventsUser';
    }
}
