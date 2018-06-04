<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 03.06.18
 * Time: 23:03
 */
namespace Search\DataManager;

use Doctrine\DBAL\Query\QueryBuilder;
use KGzocha\Searcher\Criteria\Collection\CriteriaCollection;
use KGzocha\Searcher\Criteria\CriteriaInterface;
use KGzocha\Searcher\CriteriaBuilder\Collection\CriteriaBuilderCollection;
use KGzocha\Searcher\CriteriaBuilder\CriteriaBuilderInterface;
use KGzocha\Searcher\Searcher;
use Repositiory\AbstractRepository;
use Repositiory\EventRepository;
use Search\Adapter\SearchingContextDoctrineDBALAdapter;

/**
 * Class EventSearchDataManager
 */
class EventSearchDataManager
{
    /**
     * @var Searcher|null
     */
    protected $searcher = null;

    /**
     * @var CriteriaCollection|null
     */
    protected $criteriaCollection = null;

    /**
     * @var CriteriaBuilderCollection|null
     */
    protected $builderCollection = null;

    /**
     * @var null|SearchingContextDoctrineDBALAdapter
     */
    protected $context = null;

    /**
     * EventSearchDataManager constructor.
     * @param array           $builders
     * @param array           $criterias
     * @param EventRepository $eventRepository
     */
    public function __construct(array $builders, array $criterias, QueryBuilder $qb)
    {
        $this->criteriaCollection = new CriteriaCollection();
        $this->builderCollection = new CriteriaBuilderCollection();
        $this->context = new SearchingContextDoctrineDBALAdapter($qb);

        $this->addCriteriaBuilders($builders);

        $this->addCriterias($criterias);

        $searcher = new Searcher($this->builderCollection, $this->context);

        $this->searcher = $searcher;
    }

    /**
     * Search in given context
     *
     * @return mixed
     */
    public function search()
    {
        return $this->searcher->search($this->criteriaCollection);
    }

    /**
     * @param array $builderCollection
     */
    protected function addCriteriaBuilders(array $builderCollection)
    {
        if (!$this->elementsHaveInstance($builderCollection, CriteriaBuilderInterface::class)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Every array element of 1st argument of class %s construct should implements %s',
                    __CLASS__,
                    CriteriaBuilderInterface::class
                )
            );
        }

        foreach ($builderCollection as $builder) {
            $this->builderCollection->addCriteriaBuilder($builder);
        }
    }

    /**
     * @param array $criterias
     */
    protected function addCriterias(array $criterias)
    {
        if (!$this->elementsHaveInstance($criterias, CriteriaInterface::class)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Every array element of 1st argument of class %s construct should implements %s',
                    __CLASS__,
                    CriteriaInterface::class
                )
            );
        }

        foreach ($criterias as $criteria) {
            $this->criteriaCollection->addCriteria($criteria);
        }
    }

    /**
     * @param array  $array    to search in
     * @param string $instance to check
     *
     * @return bool
     */
    protected function elementsHaveInstance(array $array, string $instance)
    {
        foreach ($array as $element) {
            if (!$element instanceof $instance) {
                return false;
            }
        }

        return true;
    }
}
