<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 17.06.18
 * Time: 18:23
 */
//todo usuń lub zmień
namespace Security;

use Security\Core\User\MyEventsUser;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Exception\InvalidArgumentException;

/**
 * Class CalendarVoter
 */
class CalendarVoter extends Voter
{
    const CALENDAR_ADMIN = 'calendar_admin';
    const CALENDAR_EDITOR = 'calendar_editor';
    const CALENDAR_ANY_USER = 'calendar_any_user';

    /**
     * Check if isGranted parameters are supported
     * @param string $attribute
     *
     * @param mixed  $subject
     *
     * @return bool
     */
    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::CALENDAR_ADMIN, self::CALENDAR_EDITOR, self::CALENDAR_ANY_USER))) {
            return false;
        }

        if (!ctype_digit((string) $subject)) {
            return false;
        }

        return true;
    }

    /**
     * Vote for permission to attribute
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

        $user = $token->getUser();

        if ($user instanceof MyEventsUser) {
            foreach ($user->getUserCalendars() as $calendarId => $role) {
                if ((int) $subject === $calendarId) {
                    if (self::CALENDAR_ANY_USER === $attribute) {
                        return true;
                    }
                    if ($attribute === $role) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
