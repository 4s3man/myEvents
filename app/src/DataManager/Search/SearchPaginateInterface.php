<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 09.06.18
 * Time: 19:41
 */

namespace DataManager\Search;

use Pagerfanta\Pagerfanta;

/**
 * Interface SearchInterface
 * used for searching and paginating
 */
interface SearchPaginateInterface
{
    /**
     * Gets query params,
     *
     * @param array      $queryParams
     * @param array|null $searchData
     *
     * @return Pagerfanta
     */
    public function getSearchedAndPaginatedRecords(array $queryParams, array $searchData = null);
}
