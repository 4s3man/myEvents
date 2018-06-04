<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 03.06.18
 * Time: 20:16
 */
namespace Search\Criteria;

use KGzocha\Searcher\Criteria\CriteriaInterface;

/**
 * Class TypeCriteria
 */
class TypeCriteria implements CriteriaInterface
{
    /**
     * @var string
     */
    private $type;

    /**
     * @var array
     */
    private $allowedTypes = ['all', 'daily', 'weekly', 'monthly', 'non_recurrent', 'recurrent'];

    /**
     * TypeCriteria constructor.
     *
     * @param string $type
     */
    public function __construct(string $type)
    {
        if (!in_array($type, $this->allowedTypes)) {
            throw new \InvalidArgumentException(sprintf('Type needs to be one of "%s"', implode('","', $this->allowedTypes)));
        }

        $this->type = $type;
    }

    /**
     * @return bool
     */
    public function shouldBeApplied()
    {
        return 'all' !== $this->type;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }
}
