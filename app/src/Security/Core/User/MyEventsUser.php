<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 16.06.18
 * Time: 23:57
 */
namespace Security\Core\User;

use \Symfony\Component\Security\Core\User\AdvancedUserInterface;

/**
 * User is the user implementation used by the in-memory user provider.
 *
 * This should not be used for anything else.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
final class MyEventsUser implements AdvancedUserInterface
{
    private $id;
    private $username;
    private $password;
    private $enabled;
    private $accountNonExpired;
    private $credentialsNonExpired;
    private $accountNonLocked;
    private $roles;
    private $userCalendars;

    /**
     * MyEventsUser constructor.
     * @param int    $id
     *
     * @param string $username
     * @param string $password
     *
     * @param array  $roles
     *
     * @param bool   $enabled
     * @param bool   $userNonExpired
     * @param bool   $credentialsNonExpired
     * @param bool   $userNonLocked
     */
    public function __construct($id, $username, $password, array $roles = array(), $userCalendars = array(), $enabled = true, $userNonExpired = true, $credentialsNonExpired = true, $userNonLocked = true)
    {
        if ('' === $username || null === $username) {
            throw new \InvalidArgumentException('The username cannot be empty.');
        }

        $this->id = $id;
        $this->username = $username;
        $this->password = $password;
        $this->enabled = $enabled;
        $this->accountNonExpired = $userNonExpired;
        $this->credentialsNonExpired = $credentialsNonExpired;
        $this->accountNonLocked = $userNonLocked;
        $this->roles = $roles;
        $this->userCalendars = $userCalendars;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getUsername();
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @return array
     */
    public function getUserCalendars()
    {
        return $this->userCalendars;
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonExpired()
    {
        return $this->accountNonExpired;
    }

    /**
     * {@inheritdoc}
     */
    public function isAccountNonLocked()
    {
        return $this->accountNonLocked;
    }

    /**
     * {@inheritdoc}
     */
    public function isCredentialsNonExpired()
    {
        return $this->credentialsNonExpired;
    }

    /**
     * {@inheritdoc}
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
    }
}
