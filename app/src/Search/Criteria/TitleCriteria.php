<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 03.06.18
 * Time: 20:16
 */
namespace Search\Criteria;

use KGzocha\Searcher\Criteria\CriteriaInterface;

/**
 * Class TitleCriteria
 */
class TitleCriteria implements CriteriaInterface
{
    /**
     * @var null
     */
    private $title = null;

    /**
     * TitleCriteria constructor.
     *
     * @param null $title
     */
    public function __construct($title)
    {
        $this->title = $title;
    }

    /**
     * @return bool
     */
    public function shouldBeApplied()
    {
        return null !== $this->title;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }
}
