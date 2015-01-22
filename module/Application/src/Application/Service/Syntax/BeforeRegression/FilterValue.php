<?php

namespace Application\Service\Syntax\BeforeRegression;

use Application\Model\Rule\AbstractQuestionnaireUsage;
use Application\Service\Calculator\Calculator;
use Application\Service\Syntax\Parser;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Replace {F#12,Q#34,P#56} with Filter value
 */
class FilterValue extends AbstractToken implements \Application\Service\Syntax\NeedHighlightColorInterface
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
        $alreadyUsedFormulasForFilter = $this->keepOnlyFilter($alreadyUsedFormulas, $filterId);
        $value = $calculator->computeFilter($filterId, $questionnaireId, $partId, $useSecondStepRules, $alreadyUsedFormulasForFilter);

        return is_null($value) ? 'NULL' : $value;
    }

    /**
     * Keep only the usages that relate to the filter we are going to compute
     * @param ArrayCollection $alreadyUsedFormulas
     * @param integer $filterId
     * @return ArrayCollection
     */
    private function keepOnlyFilter(ArrayCollection $alreadyUsedFormulas, $filterId)
    {
        $alreadyUsedFormulasForFilter = new ArrayCollection();
        foreach ($alreadyUsedFormulas as $u) {
            if ($u->getFilter() && $u->getFilter()->getId() == $filterId) {
                $alreadyUsedFormulasForFilter->add($u);
            }
        }

        return $alreadyUsedFormulasForFilter;
    }

    public function getStructure(array $matches, Parser $parser)
    {
        return [
            'type' => 'filterValue',
            'filter' => [
                'id' => $matches[1],
                'name' => $parser->getFilterName($matches[1]),
            ],
            'questionnaire' => [
                'id' => $matches[2],
                'name' => $parser->getQuestionnaireName($matches[2]),
            ],
            'part' => [
                'id' => $matches[3],
                'name' => $parser->getPartName($matches[3]),
            ],
            'isSecondStep' => isset($matches[4]) && $matches[4] == ',S#2',
        ];
    }

}
