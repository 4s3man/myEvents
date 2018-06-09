<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 03.06.18
 * Time: 20:41
 */
namespace Search\CriteriaBuilder;

use KGzocha\Searcher\Context\SearchingContextInterface;
use KGzocha\Searcher\Criteria\CriteriaInterface;
use KGzocha\Searcher\CriteriaBuilder\CriteriaBuilderInterface;
use Search\Adapter\SearchingContextDoctrineDBALAdapter;
use Search\Criteria\TitleCriteria;

/**
 * Class TitleCriteriaBuilder
 */
class TitleCriteriaBuilder implements CriteriaBuilderInterface
{
    /**
     *
     * @var null
     */
    public $alias = null;

    /**
     * TitleCriteriaBuilder constructor.
     *
     * @param null $alias
     */
    public function __construct($alias)
    {
        $this->alias = $alias;
    }

    /**
     *
     * @param CriteriaInterface         $criteria
     * @param SearchingContextInterface $searchingContext
     */
    public function buildCriteria(CriteriaInterface $criteria, SearchingContextInterface $searchingContext)
    {
            $searchingContext->getQueryBuilder()
                ->andWhere($searchingContext->getQueryBuilder()->expr()->like($this->alias.'.title', ':title'))
                ->setParameter(':title', $criteria->getTitle().'%');
    }

    /**
     *
     * @param CriteriaInterface $criteria
     *
     * @return bool
     */
    public function allowsCriteria(CriteriaInterface $criteria)
    {
        return $criteria instanceof TitleCriteria;
    }

    /**
     *
     * @param SearchingContextInterface $searchingContext
     *
     * @return bool
     */
    public function supportsSearchingContext(SearchingContextInterface $searchingContext)
    {
        return $searchingContext instanceof SearchingContextDoctrineDBALAdapter;
    }
}
