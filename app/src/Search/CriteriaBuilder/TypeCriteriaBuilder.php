<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 03.06.18
 * Time: 20:41
 */
namespace Search\CriteriaBuilder;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use KGzocha\Searcher\Context\SearchingContextInterface;
use KGzocha\Searcher\Criteria\CriteriaInterface;
use KGzocha\Searcher\CriteriaBuilder\CriteriaBuilderInterface;
use Search\Adapter\SearchingContextDoctrineDBALAdapter;
use Search\Criteria\TypeCriteria;

class TypeCriteriaBuilder implements CriteriaBuilderInterface
{
    public function buildCriteria(
        CriteriaInterface $criteria,
        SearchingContextInterface $searchingContext)
    {
            $searchingContext->getQueryBuilder()
            ->andWhere('type = :type')
            ->setParameter(':type', $criteria->getType());
    }

    public function allowsCriteria(CriteriaInterface $criteria)
    {
        return $criteria instanceof TypeCriteria;
    }

    public function supportsSearchingContext(SearchingContextInterface $searchingContext)
    {
        return $searchingContext instanceof SearchingContextDoctrineDBALAdapter;
    }
}
