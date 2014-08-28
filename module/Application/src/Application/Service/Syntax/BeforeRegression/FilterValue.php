<?php

namespace Application\Service\Syntax\BeforeRegression;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Service\Calculator\Calculator;
use Application\Model\Rule\AbstractQuestionnaireUsage;
use Application\Service\Syntax\Parser;

/**
 * Replace {F#12,Q#34,P#56} with Filter value
 */
class FilterValue extends AbstractToken
{

    public function getPattern()
    {
        return '/\{F#(\d+|current),Q#(\d+|current),P#(\d+|current)(,S#2)?\}/';
    }

    public function replace(Calculator $calculator, array $matches, AbstractQuestionnaireUsage $usage, ArrayCollection $alreadyUsedFormulas, $useSecondStepRules)
    {
        $filterId = $this->getFilterId($matches[1], $usage);
        $questionnaireId = $this->getQuestionnaireId($matches[2], $usage);
        $partId = $this->getPartId($matches[3], $usage);

        $useSecondStepRules = isset($matches[4]) && $matches[4] == ',S#2';
        $value = $calculator->computeFilter($filterId, $questionnaireId, $partId, $useSecondStepRules, $alreadyUsedFormulas);

        return is_null($value) ? 'NULL' : $value;
    }

    public function getStructure(array $matches, Parser $parser)
    {
        return [
            'type' => 'filterValue',
            'filter' => $parser->getFilterName($matches[1]),
            'questionnaire' => $parser->getQuestionnaireName($matches[2]),
            'part' => $parser->getPartName($matches[3]),
            'isSecondStep' => isset($matches[4]) && $matches[4] == ',S#2',
            'color' => $parser->getFilterColor($matches[1]),
        ];
    }

}
