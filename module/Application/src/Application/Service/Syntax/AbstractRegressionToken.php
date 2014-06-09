<?php

namespace Application\Service\Syntax;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Service\Calculator\Jmp;

abstract class AbstractRegressionToken extends AbstractToken
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
     * @param \Application\Service\Calculator\Jmp $calculator
     * @param array $matches
     * @param integer $currentFilterId
     * @param array $questionnaires
     * @param integer $currentPartId
     * @param integer $year
     * @param array $years
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadyUsedRules
     * @return float|string
     */
    abstract public function replace(Jmp $calculator, array $matches, $currentFilterId, array $questionnaires, $currentPartId, $year, array $years, ArrayCollection $alreadyUsedRules);
}
