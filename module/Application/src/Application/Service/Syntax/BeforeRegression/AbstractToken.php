<?php

namespace Application\Service\Syntax\BeforeRegression;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Service\Calculator\Calculator;
use Application\Model\Rule\AbstractQuestionnaireUsage;

abstract class AbstractToken extends \Application\Service\Syntax\AbstractToken
{

    /**
     * Returns the ID according to 'current' syntax
     * @param string|integer $filterId
     * @param \Application\Model\Rule\AbstractQuestionnaireUsage $usage
     * @return integer
     */
    protected function getFilterId($filterId, AbstractQuestionnaireUsage $usage)
    {
        if ($filterId == 'current') {
            return $usage->getFilter()->getId();
        } else {
            return $filterId;
        }
    }

    /**
     * Returns the ID according to 'current' syntax
     * @param string|integer $questionnaireId
     * @param \Application\Model\Rule\AbstractQuestionnaireUsage $usage
     * @return integer
     */
    protected function getQuestionnaireId($questionnaireId, AbstractQuestionnaireUsage $usage)
    {
        if ($questionnaireId == 'current') {
            return $usage->getQuestionnaire()->getId();
        } else {
            return $questionnaireId;
        }
    }

    /**
     * Returns the ID according to 'current' syntax
     * @param string|integer $partId
     * @param \Application\Model\Rule\AbstractQuestionnaireUsage $usage
     * @return integer
     */
    protected function getPartId($partId, AbstractQuestionnaireUsage $usage)
    {
        if ($partId == 'current') {
            return $usage->getPart()->getId();
        } else {
            return $partId;
        }
    }

    /**
     *
     * @param \Application\Service\Calculator\Calculator $calculator
     * @param array $matches
     * @param \Application\Model\Rule\AbstractQuestionnaireUsage $usage
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadyUsedFormulas
     * @param boolean $useSecondLevelRules
     * @return float|string
     */
    abstract public function replace(Calculator $calculator, array $matches, AbstractQuestionnaireUsage $usage, ArrayCollection $alreadyUsedFormulas, $useSecondLevelRules);
}
