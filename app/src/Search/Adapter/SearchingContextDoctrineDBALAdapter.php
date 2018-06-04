<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 03.06.18
 * Time: 21:11
 */

namespace Search\Adapter;

use Doctrine\DBAL\Query\QueryBuilder;
use KGzocha\Searcher\Context\SearchingContextInterface;

/**
 * Class SearchingContextDoctrineDBALAdapter
 *
 * Adapts DoctrineDbal witch SearchingContextInterface
 * and since getResults returns queryBuilder with pagerfanta
 *
 */
class SearchingContextDoctrineDBALAdapter implements SearchingContextInterface
{
    /**
     * @var QueryBuilder
     */
    private $qb;

    /**
     * SearchingContextDoctrineDBALAdapter constructor.
     * @param QueryBuilder $qb
     */
    public function __construct(QueryBuilder $qb)
    {
        $this->qb = $qb;
    }

    /**
     * Used in CriteriaBuilder
     *
     * @return QueryBuilder|mixed
     */
    public function getQueryBuilder()
    {
        return $this->qb;
    }

    /**
     * Returning queryBuilder becouse Pagerfanta Paginator needs it
     *
     * @return QueryBuilder|mixed
     */
    public function getResults()
    {
        return $this->getQueryBuilder();
    }
}
