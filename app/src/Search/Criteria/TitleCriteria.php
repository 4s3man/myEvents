<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 03.06.18
 * Time: 20:16
 */
namespace Search\Criteria;

use KGzocha\Searcher\Criteria\CriteriaInterface;

class TitleCriteria implements CriteriaInterface
{
    private $title;

    public function shouldBeApplied()
    {
        return null !== $this->title;
    }

    /**
     * @param mixed $type
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }
}