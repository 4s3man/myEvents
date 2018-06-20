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

/**
 * Class CalendarVoter
 */
class CalendarVoter extends Voter
{
    const CALENDAR_ADMIN = 'calendar_admin';
    const CALENDAR_EDITOR = 'calendar_editor';
    const CALENDAR_ANY_USER = 'calendar_any_user';

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::CALENDAR_ADMIN, self::CALENDAR_EDITOR, self::CALENDAR_ANY_USER))) {
            return false;
        }

        //todo dodać return false if subject is not instance of what i want

        return true;
    }

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
                if ((int)$subject === $calendarId) {
                    if ($attribute === self::CALENDAR_ANY_USER) {

                        return true;
                    } elseif ($attribute === $role) {

                        return true;
                    }
                }
            }
        }

        return false;
    }
}
