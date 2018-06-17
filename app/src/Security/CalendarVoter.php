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

/**
 * Class CalendarVoter
 */
class CalendarVoter extends Voter
{
    const CALENDAR_ADMIN = 'calendar_admin';
    const CALENDAR_USER = 'calendar_user';

    protected function supports($attribute, $subject)
    {
        if (!in_array($attribute, array(self::CALENDAR_ADMIN, self::CALENDAR_USER))) {
            return false;
        }

        //todo dodać return false if subject is not instance of what i want

        return true;
    }

    protected function voteOnAttribute($attribute, $subject, TokenInterface $token)
    {
        dump($subject);

        return true;

        throw new \LogicException('This code should not be reached!');
    }
}
