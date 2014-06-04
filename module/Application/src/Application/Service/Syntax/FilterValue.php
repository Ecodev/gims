<?php

namespace Application\Service\Syntax;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Service\Calculator\Calculator;
use Application\Model\Rule\AbstractQuestionnaireUsage;

/**
 * Replace {F#12,Q#34,P#56} with Filter value
 */
class FilterValue extends AbstractBasicToken
{

    public function getPattern()
    {
        return '/\{F#(\d+|current),Q#(\d+|current),P#(\d+|current)(,L#2)?\}/';
    }

    public function replace(Calculator $calculator, array $matches, AbstractQuestionnaireUsage $usage, ArrayCollection $alreadyUsedFormulas, $useSecondLevelRules)
    {
        $filterId = $matches[1];
        $questionnaireId = $matches[2];
        $partId = $matches[3];

        if ($filterId == 'current') {
            $filterId = $usage->getFilter()->getId();
        }

        if ($questionnaireId == 'current') {
            $questionnaireId = $usage->getQuestionnaire()->getId();
        }

        if ($partId == 'current') {
            $partId = $usage->getPart()->getId();
        }

        $useSecondLevelRules = isset($matches[4]) && $matches[4] == ',L#2';
        $value = $calculator->computeFilter($filterId, $questionnaireId, $partId, $useSecondLevelRules, $alreadyUsedFormulas);

        return is_null($value) ? 'NULL' : $value;
    }

}
