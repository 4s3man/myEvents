<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 03.05.18
 * Time: 22:12
 */

namespace Utils;

use Pagerfanta\Pagerfanta;
use Pagerfanta\View\TwitterBootstrap4View;
use Pagerfanta\Adapter\DoctrineDbalAdapter;
use Doctrine\DBAL\Query\QueryBuilder;
use Silex\Application;

/**
 * Class DataPaginator
 */
class MyPaginatorShort
{
    public $paginator;
    public $data;

    /**
     * MyPaginatorShort constructor.
     * @param Application $app
     * @param QueryBuilder $queryAll
     * @param $maxPerPage
     * @param string $url
     * @param int $page
     */
    public function __construct(Application $app, QueryBuilder $queryAll, $maxPerPage, $url, $page = 1)
    {
        $modifier = function ($queryBuilder) {
            $queryBuilder->select('COUNT(DISTINCT id) AS total_results')
                ->setMaxResults(1);
        };

        $routeGenerator = function ($page) use ($app, $url) {

            return $app['url_generator']->generate($url, ['page' => $page]);
        };
        $view = new TwitterBootstrap4View();

        $adapter = new DoctrineDbalAdapter($queryAll, $modifier);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta->setMaxPerPage($maxPerPage);
        $page = $this->assertCurrentPageOk($pagerfanta, $page);
        $pagerfanta->setCurrentPage($page);
        $options = array(
                'prev_message' => '&larr;'.$app['translator']->trans('paginator.prev'),
                'next_message' => $app['translator']->trans('paginator.next').'&rarr;',
            );
        $this->paginator = $view->render($pagerfanta, $routeGenerator, $options);
        $this->data = $pagerfanta->getCurrentPageResults();
    }

    /**
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
