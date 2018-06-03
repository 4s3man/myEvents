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

class TitleCriteriaBuilder implements CriteriaBuilderInterface
{
    public function buildCriteria(
        CriteriaInterface $criteria,
        SearchingContextInterface $searchingContext)
    {
            $searchingContext->getQueryBuilder()
                ->andWhere($searchingContext->getQueryBuilder()->expr()->like('e.title', ':title'))
                ->setParameter(':title', $criteria->getTitle().'%');
    }

    public function allowsCriteria(CriteriaInterface $criteria)
    {
        return $criteria instanceof TitleCriteria;
    }

    public function supportsSearchingContext(SearchingContextInterface $searchingContext)
    {
        return $searchingContext instanceof SearchingContextDoctrineDBALAdapter;
    }
}
