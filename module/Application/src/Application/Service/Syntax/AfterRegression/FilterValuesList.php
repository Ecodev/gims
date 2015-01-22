<?php

namespace Application\Service\Syntax\AfterRegression;

use Application\Service\Calculator\Calculator;
use Application\Service\Syntax\Parser;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Replace {F#12,Q#all} with a list of Filter values for all questionnaires
 */
class FilterValuesList extends AbstractToken implements \Application\Service\Syntax\BothContextInterface
{

    public function getPattern()
    {
        return '/\{F#(\d+|current),Q#all\}/';
    }

    public function replace(Calculator $calculator, array $matches, $currentFilterId, array $questionnaires, $currentPartId, $year, ArrayCollection $alreadyUsedRules)
    {
        $filterId = $this->getId($matches[1], $currentFilterId);

        $data = $calculator->computeFilterForAllQuestionnaires($filterId, $questionnaires, $currentPartId);

        $values = [];
        foreach ($data['values'] as $v) {
            if (!is_null($v)) {
                $values[] = $v;
            }
        }

        $values = '{' . implode(', ', $values) . '}';

        return $values;
    }

    public function getStructure(array $matches, Parser $parser)
    {
        return [
            'type' => 'regressionFilterValuesList',
            'filter' => [
                'id' => $matches[1],
                'name' => $parser->getFilterName($matches[1]),
            ],
        ];
    }

}
