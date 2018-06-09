<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 09.06.18
 * Time: 18:38
 */

namespace DataManager\Search;;

use Doctrine\DBAL\Query\QueryBuilder;

class SearchDataManager
{
    /**
     * @var QueryBuilder|null
     */
    private $query = null;

    /**
     * @var array $allowedKeys
     */
    private $allowedKeys = ['title', 'dateRange', 'signUp', 'user_role', 'seatsRange', 'costRange'];

    /**
     * SearchDataManager constructor.
     * @param null $query
     */
    public function __construct(QueryBuilder $query,array $keys = null)
    {
        $this->query = $query;
        if (is_array($keys)) {
            $this->checkKeys($keys);
            $this->query = $this->addFilters($keys);
        } elseif (null !== $keys) {
            throw new \InvalidArgumentException(sprintf('2 argument of %s construct needs to be array or null', __CLASS__));
        }
    }

    /**
     * @return QueryBuilder|null
     */
    public function getQuery()
    {
        return $this->query;
    }

    private function addFilters($searchData)
    {
        foreach ($searchData as $key => $val) {
            switch ($key) {
                case 'title':
                    if ($val) {
                        $this->query->andWhere('title like :title')
                        ->setParameter(':title', $val.'%', \PDO::PARAM_STR);
                    }
                    break;
                case 'signUp':

                    break;
            }
        }
    }

    private function checkKeys($searchData)
    {
        $keys = array_keys($searchData);
        foreach ($keys as $key) {
            if (!in_array($key, $this->allowedKeys)) {
                throw new \InvalidArgumentException(
                    sprintf('Unallowed argument! Arguments passed to class %s construct, have to be one of "%s"',
                        __CLASS__,
                        implode('","', $this->allowedKeys)
                    )
                );
            }
        }
    }
}