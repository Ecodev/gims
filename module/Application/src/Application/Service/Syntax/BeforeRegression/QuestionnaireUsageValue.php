<?php

namespace Application\Service\Syntax\BeforeRegression;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Service\Calculator\Calculator;
use Application\Model\Rule\AbstractQuestionnaireUsage;
use Application\Service\Syntax\Parser;

/**
 * Replace {R#12,Q#34,P#56} with QuestionnaireUsage value
 */
class QuestionnaireUsageValue extends AbstractToken
{

    public function getPattern()
    {
        return '/\{R#(\d+),Q#(\d+|current),P#(\d+|current)\}/';
    }

    public function replace(Calculator $calculator, array $matches, AbstractQuestionnaireUsage $usage, ArrayCollection $alreadyUsedFormulas, $useSecondStepRules)
    {
        $ruleId = $matches[1];
        $questionnaireId = $this->getQuestionnaireId($matches[2], $usage);
        $partId = $this->getPartId($matches[3], $usage);

        $questionnaireUsage = $calculator->getQuestionnaireUsageRepository()->getOneByQuestionnaire($questionnaireId, $partId, $ruleId);

        if (!$questionnaireUsage) {
            throw new \Exception('Reference to non existing QuestionnaireUsage ' . $matches[0] . ' in  Rule#' . $usage->getRule()->getId() . ', "' . $usage->getRule()->getName() . '": ' . $usage->getRule()->getFormula());
        }

        $value = $calculator->computeFormulaBeforeRegression($questionnaireUsage, $alreadyUsedFormulas);

        return is_null($value) ? 'NULL' : $value;
    }

    public function getStructure(array $matches, Parser $parser)
    {
        $rule = $parser->getRuleRepository()->findOneById($matches[1]);

        return [
            'type' => 'ruleValue',
            'rule' => $rule->getName(),
            'questionnaire' => $parser->getQuestionnaireName($matches[2]),
            'part' => $parser->getPartName($matches[3]),
        ];
    }

}
