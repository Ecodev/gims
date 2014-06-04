<?php

namespace Application\Service\Syntax;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Service\Calculator\Calculator;
use Application\Model\Rule\AbstractQuestionnaireUsage;

/**
 * Replace {self} with computed value without this formula
 */
class BasicSelf extends AbstractBasicToken
{

    public function getPattern()
    {
        return '/\{self\}/';
    }

    public function replace(Calculator $calculator, array $matches, AbstractQuestionnaireUsage $usage, ArrayCollection $alreadyUsedFormulas, $useSecondLevelRules)
    {
        $value = $calculator->computeFilter($usage->getFilter()->getId(), $usage->getQuestionnaire()->getId(), $usage->getPart()->getId(), $useSecondLevelRules, $alreadyUsedFormulas);

        return is_null($value) ? 'NULL' : $value;
    }

}
