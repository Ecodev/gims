<?php

namespace Application\Service\Syntax\AfterRegression;

use Application\Service\Calculator\Calculator;
use Doctrine\Common\Collections\ArrayCollection;

abstract class AbstractToken extends \Application\Service\Syntax\AbstractToken
{

    /**
     * Returns the ID according to 'current' syntax
     * @param string|integer $id
     * @param integer $currentId
     * @return integer
     */
    protected function getId($id, $currentId)
    {
        if ($id == 'current') {
            return $currentId;
        } else {
            return $id;
        }
    }

    /**
     *
     * @param \Application\Service\Calculator\Calculator $calculator
     * @param array $matches
     * @param integer $currentFilterId
     * @param array $questionnaires
     * @param integer $currentPartId
     * @param integer $year
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadyUsedRules
     * @return float|string
     */
    abstract public function replace(Calculator $calculator, array $matches, $currentFilterId, array $questionnaires, $currentPartId, $year, ArrayCollection $alreadyUsedRules);
}
