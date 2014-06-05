<?php

namespace Application\Service\Syntax;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Service\Calculator\Calculator;
use Application\Model\Rule\AbstractQuestionnaireUsage;

/**
 * Replace {R#12,Q#34,P#56} with QuestionnaireUsage value
 */
class QuestionnaireUsageValue extends AbstractBasicToken
{

    public function getPattern()
    {
        return '/\{R#(\d+),Q#(\d+|current),P#(\d+|current)\}/';
    }

    public function replace(Calculator $calculator, array $matches, AbstractQuestionnaireUsage $usage, ArrayCollection $alreadyUsedFormulas, $useSecondLevelRules)
    {
        $ruleId = $matches[1];
        $questionnaireId = $matches[2];
        $partId = $matches[3];

        if ($questionnaireId == 'current') {
            $questionnaireId = $usage->getQuestionnaire()->getId();
        }

        if ($partId == 'current') {
            $partId = $usage->getPart()->getId();
        }

        $questionnaireUsage = $calculator->getQuestionnaireUsageRepository()->getOneByQuestionnaire($questionnaireId, $partId, $ruleId);

        if (!$questionnaireUsage) {
            throw new \Exception('Reference to non existing QuestionnaireUsage ' . $matches[0] . ' in  Rule#' . $usage->getRule()->getId() . ', "' . $usage->getRule()->getName() . '": ' . $usage->getRule()->getFormula());
        }

        $value = $calculator->computeFormulaBasic($questionnaireUsage, $alreadyUsedFormulas);

        return is_null($value) ? 'NULL' : $value;
    }

}
