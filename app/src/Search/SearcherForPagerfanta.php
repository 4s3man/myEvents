<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 04.06.18
 * Time: 00:01
 */
namespace Search;

use KGzocha\Searcher\Context\SearchingContextInterface;
use KGzocha\Searcher\CriteriaBuilder\Collection\CriteriaBuilderCollectionInterface;
use KGzocha\Searcher\Criteria\Collection\CriteriaCollectionInterface;
use KGzocha\Searcher\Criteria\CriteriaInterface;
use KGzocha\Searcher\SearcherInterface;

class SearcherForPagerfanta implements SearcherInterface
{
    //TODO pytanie czy dałoby się to zrobić extendem jeśli klasa ma prywatne stałe? :<
    /**
     * @var CriteriaBuilderCollectionInterface
     */
    private $builders;

    /**
     * @var SearchingContextInterface
     */
    private $searchingContext;

    /**
     * @param CriteriaBuilderCollectionInterface $builders
     * @param SearchingContextInterface          $searchingContext
     */
    public function __construct(
        CriteriaBuilderCollectionInterface $builders,
        SearchingContextInterface $searchingContext
    ) {
        $this->builders = $builders;
        $this->searchingContext = $searchingContext;
    }

    /**
     * {@inheritdoc}
     */
    public function search(
        CriteriaCollectionInterface $criteriaCollection
    ) {
        $builders = $this
            ->builders
            ->getCriteriaBuildersForContext($this->searchingContext);
        foreach ($criteriaCollection->getApplicableCriteria() as $criteria) {
            $this->searchForModel($criteria, $this->searchingContext, $builders);
        }

        return $this->searchingContext->getQueryBuilder();
    }

    /**
     * @param CriteriaInterface                  $criteria
     * @param SearchingContextInterface          $searchingContext
     * @param CriteriaBuilderCollectionInterface $builders
     */
    private function searchForModel(
        CriteriaInterface $criteria,
        SearchingContextInterface $searchingContext,
        CriteriaBuilderCollectionInterface $builders
    ) {
        foreach ($builders as $builder) {
            if ($builder->allowsCriteria($criteria)) {
                $builder->buildCriteria($criteria, $searchingContext);
            }
        }
    }
}
