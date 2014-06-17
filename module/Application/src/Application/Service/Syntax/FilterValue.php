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
        $filterId = $this->getFilterId($matches[1], $usage);
        $questionnaireId = $this->getQuestionnaireId($matches[2], $usage);
        $partId = $this->getPartId($matches[3], $usage);

        $useSecondLevelRules = isset($matches[4]) && $matches[4] == ',L#2';
        $value = $calculator->computeFilter($filterId, $questionnaireId, $partId, $useSecondLevelRules, $alreadyUsedFormulas);

        return is_null($value) ? 'NULL' : $value;
    }

    public function getStructure(array $matches, Parser $parser)
    {
        return [
            'type' => 'filterValue',
            'filter' => $this->getFilterName($matches[1], $parser),
            'questionnaire' => $this->getQuestionnaireName($matches[2], $parser),
            'part' => $this->getPartName($matches[3], $parser),
            'level' => isset($matches[4]) && $matches[4] == ',L#2',
            'color' => $this->getFilterColor($matches[1], $parser),
        ];
    }

}
