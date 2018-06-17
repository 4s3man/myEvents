<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 17.06.18
 * Time: 18:23
 */
//todo usuń lub zmień
namespace Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

/**
 * Class CalendarVoter
 */
class UserVoter extends Voter
{
    const THIS_USER = 'this_user';

    /**
     * What strings are supported via this voter as 1st parameter of isGranted
     * @param string $attribute
     *
     * @param mixed  $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::THIS_USER))) {
            return false;
        }

        //todo dodać return false if subject is not instance of what i want

        return true;
    }

    /**
     * Conditions for isGranted
     * @param string         $attribute
     *
     * @param mixed          $subject
     *
     * @param TokenInterface $token
     *
     * @return bool
     */
    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        if (!ctype_digit((string) $subject)) {
            throw new InvalidArgumentException('2nd argument for is_Granted(\'this_user\', 2ndArg.. needs to be valid integer');
        }

        if (in_array('ROLE_SUPER_USER', $token->getRoles())) {
            return true;
        }

        if ($subject === $token->getUser()->getId()) {
            return true;
        }

        return false;
    }
}
