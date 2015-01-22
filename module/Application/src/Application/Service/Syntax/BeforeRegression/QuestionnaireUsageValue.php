<?php

namespace Application\Service\Syntax\BeforeRegression;

use Application\Model\Rule\AbstractQuestionnaireUsage;
use Application\Service\Calculator\Calculator;
use Application\Service\Syntax\Parser;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Replace {R#12,Q#34,P#56} with QuestionnaireUsage value
 */
class QuestionnaireUsageValue extends AbstractToken implements \Application\Service\Syntax\NeedHighlightColorInterface
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
            'rule' => [
                'id' => $rule->getId(),
                'name' => $rule->getName(),
            ],
            'questionnaire' => [
                'id' => $matches[2],
                'name' => $parser->getQuestionnaireName($matches[2]),
            ],
            'part' => [
                'id' => $matches[3],
                'name' => $parser->getPartName($matches[3]),
            ],
        ];
    }

}
