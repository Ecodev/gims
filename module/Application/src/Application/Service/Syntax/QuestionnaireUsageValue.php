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
        $questionnaireId = $this->getQuestionnaireId($matches[2], $usage);
        $partId = $this->getPartId($matches[3], $usage);

        $questionnaireUsage = $calculator->getQuestionnaireUsageRepository()->getOneByQuestionnaire($questionnaireId, $partId, $ruleId);

        if (!$questionnaireUsage) {
            throw new \Exception('Reference to non existing QuestionnaireUsage ' . $matches[0] . ' in  Rule#' . $usage->getRule()->getId() . ', "' . $usage->getRule()->getName() . '": ' . $usage->getRule()->getFormula());
        }

        $value = $calculator->computeFormulaBasic($questionnaireUsage, $alreadyUsedFormulas);

        return is_null($value) ? 'NULL' : $value;
    }

}
