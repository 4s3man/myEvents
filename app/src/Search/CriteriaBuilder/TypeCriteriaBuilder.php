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
use Search\Criteria\TypeCriteria;

/**
 * Class TypeCriteriaBuilder
 */
class TypeCriteriaBuilder implements CriteriaBuilderInterface
{
    /**
     * @param CriteriaInterface         $criteria
     *
     * @param SearchingContextInterface $searchingContext
     */
    public function buildCriteria(CriteriaInterface $criteria, SearchingContextInterface $searchingContext)
    {
        if ('recurrent' === $criteria->getType()) {
            $searchingContext->getQueryBuilder()
                ->andWhere('type != :type')
                ->setParameter(':type', 'non_recurrent');
        } else {
            $searchingContext->getQueryBuilder()
                ->andWhere('type = :type')
                ->setParameter(':type', $criteria->getType());
        }
    }

    /**
     * @param CriteriaInterface $criteria
     *
     * @return bool
     */
    public function allowsCriteria(CriteriaInterface $criteria)
    {
        return $criteria instanceof TypeCriteria;
    }

    /**
     * @param SearchingContextInterface $searchingContext
     *
     * @return bool
     */
    public function supportsSearchingContext(SearchingContextInterface $searchingContext)
    {
        return $searchingContext instanceof SearchingContextDoctrineDBALAdapter;
    }
}
