<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 20.05.18
 * Time: 21:21
 */

namespace Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints as Assert;

abstract class AbstractAssertsLibType extends AbstractType
{
    /**
     * Asserty spięte do jednej tablicy może potem
     * możnaby je wyrzucić do osobnego obiektu jakby już ich
     * było sporo powtarzających się
     *
     * @param array $groups validation groups
     *
     * @return array
     */
    protected function textAsserts($groups = ['register_default'])
    {
        return [
            new Assert\NotBlank(
                [
                    'groups' => $groups,
                ]
            ),
            new Assert\Regex(
                [
                    'groups' => $groups,
                    'pattern' => "/^[A-Za-zżźćńółęąśŻŹĆĄŚĘŁÓŃ]*$/",
                ]
            ),
            new Assert\Length(
                [
                    'groups' => $groups,
                    'max' => 45,
                ]
            ),
        ];
    }

    /**
     * Asserty spięte do jednej tablicy może potem
     * możnaby je wyrzucić do osobnego obiektu jakby już ich
     * było sporo powtarzających się
     *
     * @param array $groups validation_groups
     *
     * @return array
     */
    protected function usernameAsserts($groups = ['register_default'])
    {
        return [
            new Assert\NotBlank(
                [
                    'groups' => $groups,
                ]
            ),
            new Assert\Regex(
                [
                    'groups' => $groups,
                    'pattern' => '/^[\s\p{L}0-9]+(?:[_-][\s\p{L}0-9]+)*$/u'
                ]
            ),
        ];
    }

}