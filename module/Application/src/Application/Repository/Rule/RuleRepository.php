<?php

namespace Application\Repository\Rule;

class RuleRepository extends \Application\Repository\AbstractRepository
{

    /**
     * Returns the single instance of Exclude rule in the entire system (and create it, if not present)
     * @return \Application\Model\Rule\Exclude
     */
    public function getSingletonExclude()
    {
        $query = $this->getEntityManager()->createQuery("SELECT rule FROM Application\Model\Rule\Exclude rule");

        $rule = $query->getOneOrNullResult();
        if (!$rule) {
            $rule = new \Application\Model\Rule\Exclude();
            $this->getEntityManager()->persist($rule);
        }

        return $rule;
    }

}
