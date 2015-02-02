<?php

namespace Application\Service\Syntax\BeforeRegression;

use Application\Model\Rule\AbstractQuestionnaireUsage;
use Application\Service\Calculator\Calculator;
use Application\Service\Syntax\Parser;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Replace {F#12,Q#34} with Question name, or NULL if no Question/Answer
 */
class QuestionName extends AbstractToken
{

    public function getPattern()
    {
        return '/\{F#(\d+|current),Q#(\d+|current)\}/';
    }

    public function replace(Calculator $calculator, array $matches, AbstractQuestionnaireUsage $usage, ArrayCollection $alreadyUsedFormulas, $useSecondStepRules)
    {
        $filterId = $this->getFilterId($matches[1], $usage);
        $questionnaireId = $this->getQuestionnaireId($matches[2], $usage);

        $questionName = $calculator->getAnswerRepository()->getQuestionNameIfNonNullAnswer($questionnaireId, $filterId);
        if (is_null($questionName)) {
            return 'NULL';
        } else {
            // Format string for Excel formula
            return '"' . str_replace('"', '""', $questionName) . '"';
        }
    }

    public function getStructure(array $matches, Parser $parser)
    {
        return [
            'type' => 'questionName',
            'filter' => [
                'id' => $matches[1],
                'name' => $parser->getFilterName($matches[1]),
            ],
            'questionnaire' => [
                'id' => $matches[2],
                'name' => $parser->getQuestionnaireName($matches[2]),
            ],
        ];
    }
}
