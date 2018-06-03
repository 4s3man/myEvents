<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 03.06.18
 * Time: 23:03
 */
namespace Search\DataManager;

use KGzocha\Searcher\Criteria\Collection\CriteriaCollection;
use KGzocha\Searcher\CriteriaBuilder\Collection\CriteriaBuilderCollection;
use Repositiory\EventRepository;
use Search\Adapter\SearchingContextDoctrineDBALAdapter;
use Search\Criteria\TitleCriteria;
use Search\Criteria\TypeCriteria;
use Search\CriteriaBuilder\TitleCriteriaBuilder;
use Search\CriteriaBuilder\TypeCriteriaBuilder;
use Search\SearcherForPagerfanta;

class EventSearchDataManager
{
    protected $searcher = null;

    protected $criteriaCollection = null;

    /**
     * EventSearchDataManager constructor.
     */
    public function __construct(EventRepository $eventRepository)
    {

        $searchBuilder = new CriteriaBuilderCollection();
        $searchBuilder->addCriteriaBuilder(new TitleCriteriaBuilder());
        $searchBuilder->addCriteriaBuilder(new TypeCriteriaBuilder());

        $criteria = new CriteriaCollection();

        $eventTypeCriteria = new TypeCriteria();
        $eventTypeCriteria->setType('weekly');

        $titleCriteria = new TitleCriteria();
        $titleCriteria->setTitle('m');

        $criteria->addCriteria($eventTypeCriteria);
        $criteria->addCriteria($titleCriteria);

        $context = new SearchingContextDoctrineDBALAdapter($eventRepository->queryAll());
        $searcher = new SearcherForPagerfanta($searchBuilder, $context);

        $this->criteriaCollection = $criteria;
        $this->searcher = $searcher;
    }

    public function search()
    {
        return $this->searcher->search($this->criteriaCollection);
    }

}