<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 20.05.18
 * Time: 21:21
 */

namespace Helpers;


use Symfony\Component\Form\AbstractType;
use Symfony\Component\Validator\Constraints as Assert;

class PopularAssertsArrays
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
    public function textAsserts($groups = ['register_default'])
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
    public function usernameAsserts($groups = ['register_default'])
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