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
     * @var QueryBuilder|null
     */
    private $query = null;

    /**
     * @var array $allowedKeys
     */
    private $allowedKeys = ['title', 'email', 'user_role'];

    /**
     * SearchDataManager constructor.
     * @param QueryBuilder $query
     * @param array|null   $keys
     */
    public function __construct(QueryBuilder $query, array $keys = null)
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

    /**
     * Adds andWhere to passed query
     * @param array $searchData
     */
    private function addFilters(array $searchData)
    {
        foreach ($searchData as $key => $val) {
            if ($val) {
                $this->query->andWhere($key.' like :value')
                    ->setParameter(':value', $val.'%', \PDO::PARAM_STR);
            }
        }
    }

    /**
     * Check if keys queries are supported
     * @param array $searchData
     */
    private function checkKeys(array $searchData)
    {
        $keys = array_keys($searchData);
        foreach ($keys as $key) {
            if (!in_array($key, $this->allowedKeys)) {
                throw new \InvalidArgumentException(
                    sprintf(
                        'Unallowed argument! Arguments passed to class %s construct, have to be one of "%s"',
                        __CLASS__,
                        implode('","', $this->allowedKeys)
                    )
                );
            }
        }
    }
}
