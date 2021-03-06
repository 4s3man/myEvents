<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 03.05.18
 * Time: 22:12
 */

namespace Utils;

use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\DoctrineDbalAdapter;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Class DataPaginator
 */
class MyPaginatorShort
{
    public $pagerfanta = null;

    /**
     * MyPaginatorShort constructor.
     *
     * @param QueryBuilder $queryAll
     * @param int          $maxPerPage
     * @param string       $identifier
     * @param int          $page
     */
    public function __construct(QueryBuilder $queryAll, $maxPerPage, $identifier, $page = 1)
    {
        $modifier = function ($queryBuilder) use ($identifier) {
            $queryBuilder->select('COUNT(DISTINCT '.$identifier.') AS total_results')
                ->setMaxResults(1);
        };

        $adapter = new DoctrineDbalAdapter($queryAll, $modifier);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($maxPerPage);
        if ($pagerfanta->haveToPaginate()) {
            $page = $this->assertCurrentPageOk($pagerfanta, $page);
            $pagerfanta->setCurrentPage($page);
        }

        $this->pagerfanta = $pagerfanta;
    }

    /**
     *
     * @param Pagerfanta $pagerfanta
     *
     * @param int        $page
     *
     * @return int
     */
    private function assertCurrentPageOk(Pagerfanta $pagerfanta, $page)
    {
        $results = $pagerfanta->getNbResults();
        $maxPerPage = $pagerfanta->getMaxPerPage();
        $endPage = (int) ceil($results/$maxPerPage);
        if ($page <= $endPage && $page >= 1) {
            return $page;
        }

        return $page > $endPage ? $endPage : 1;
    }
}
