<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 20.05.18
 * Time: 21:21
 */

namespace Form\Helpers;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class PopularAssertGroups
 */
class PopularAssertGroups
{
    /**
     *
     * @var string
     */
    private $slugRegexp = '/^[\s\p{L}0-9]+(?:[_-][\s\p{L}0-9]+)*$/u';

    /**
     *
     * @var string
     */
    private $contentRegexp = '/^[^\-\"\'][^\"\';]*[^-\"\']$/';

    /**
     *
     * @var string
     */
    private $name = '/^[\p{L}]+(-[\p{L}])*$/u';

    /**
     *
     * @param array $groups
     *
     * @return array
     */
    public function slug(array $groups = [])
    {
        $slug = $this->slugOptional($groups);
        $slug[] = new Assert\NotBlank(['groups' => $groups]);

        return $slug;
    }

    /**
     *
     * @param array $groups
     *
     * @return array
     */
    public function slugOptional(array $groups = [])
    {
        return [
            new Assert\Regex(
                [
                    'groups' => $groups,
                    'pattern' => $this->slugRegexp,
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
     *
     * @param array $groups
     *
     * @return array
     */
    public function name(array $groups = [])
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
                    'pattern' => $this->name,
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
     *
     * @param array $groups
     *
     * @return array
     */
    public function title(array $groups = [])
    {
        return [
            new Assert\Regex(
                [
                    'groups' => $groups,
                    'pattern' => $this->contentRegexp,
                ]
            ),
            new Assert\Length(
                [
                    'groups' => $groups,
                    'max' => 128,
                ]
            ),
        ];
    }

    /**
     *
     * @return string
     */
    public function getContentRegexp()
    {
        return $this->contentRegexp;
    }
}
