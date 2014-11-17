<?php

namespace Application\Service\Syntax\AfterRegression;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Service\Calculator\Calculator;
use Application\Service\Syntax\Parser;

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

        $values = array();
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
            'filter' => $parser->getFilterName($matches[1]),
            'color' => $parser->getFilterColor($matches[1]),
        ];
    }

}
