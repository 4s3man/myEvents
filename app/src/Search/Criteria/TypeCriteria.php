<?php
/**
 * Created by PhpStorm.
 * User: kuba
 * Date: 03.06.18
 * Time: 20:16
 */
namespace Search\Criteria;

use KGzocha\Searcher\Criteria\CriteriaInterface;

class TypeCriteria implements CriteriaInterface
{
    private $type;

    private $allowedTypes = ['daily', 'weekly', 'monthly', 'non_recurrent'];

    public function shouldBeApplied()
    {
        return true;
    }

    /**
     * @param mixed $type
     */
    public function setType($type)
    {
        if (!in_array($type, $this->allowedTypes)) {
            throw new \InvalidArgumentException(sprintf('Type needs to be one of "%s"', implode('","',$this->allowedTypes)));
        }

        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }
}