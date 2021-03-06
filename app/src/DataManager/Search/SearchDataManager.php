<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 09.06.18
 * Time: 18:38
 */

namespace DataManager\Search;

use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Class SearchDataManager
 */
class SearchDataManager
{
    /**
     *
     * @var QueryBuilder|null
     */
    private $query = null;

    /**
     *
     * @var string|null
     */
    private $alias = null;

    /**
     * SearchDataManager constructor.
     *
     * @param QueryBuilder $query
     *
     * @param string       $searchAlias
     */
    public function __construct(QueryBuilder $query, $searchAlias = null)
    {
        $this->query = $query;
        $this->alias = $searchAlias;
    }

    /**
     *
     * @return QueryBuilder|null
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Checks what to do witch passed data
     *
     * @param array $searchData
     */
    public function addFilters($searchData)
    {
        if (is_array($searchData)) {
            $this->query = $this->filter($searchData);
        } elseif (null !== $searchData) {
            throw new \InvalidArgumentException(sprintf('2 argument of %s construct needs to be array or null', __CLASS__));
        }
    }

    /**
     * Adds andWhere filters to query passed in construct
     *
     * @param array $searchData
     */
    private function filter(array $searchData)
    {
        foreach ($searchData as $key => $val) {
            if ($val) {
                if ($this->alias) {
                    $key = $this->alias.'.'.$key;
                }
                $this->query->andWhere($key.' like :value')
                    ->setParameter(':value', $val.'%', \PDO::PARAM_STR);
            }
        }
    }
}
