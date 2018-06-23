<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 20.05.18
 * Time: 21:21
 */

namespace Form\Helpers;

/**
 * Class PopularAssertGroups
 */
class Regexps
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
     * @return string
     */
    public function getContentRegexp()
    {
        return $this->contentRegexp;
    }

    /**
     * @return string
     */
    public function getNameRegexp()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getSlugRegexp()
    {
        return $this->slugRegexp;
    }
}
