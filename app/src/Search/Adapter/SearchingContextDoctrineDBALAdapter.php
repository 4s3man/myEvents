<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 03.06.18
 * Time: 21:11
 */

namespace Search\Adapter;


use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use KGzocha\Searcher\Context\AbstractSearchingContext;
use KGzocha\Searcher\Context\SearchingContextInterface;

class SearchingContextDoctrineDBALAdapter implements SearchingContextInterface
{
    private $qb;

    /**
     * SearchingContextDoctrineDBALAdapter constructor.
     * @param $db
     */
    public function __construct(QueryBuilder $qb)
    {
        $this->qb = $qb;
    }

    public function getQueryBuilder()
    {
        return $this->qb;
    }

    public function getResults()
    {
        return $this->getQueryBuilder()->execute()->fetchAll();
    }
}