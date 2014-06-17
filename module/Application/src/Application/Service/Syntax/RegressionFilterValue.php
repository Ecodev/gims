<?php

namespace Application\Service\Syntax;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Service\Calculator\Jmp;

/**
 * Replace {F#12,P#34,Y+0} with Filter regression value
 */
class RegressionFilterValue extends AbstractRegressionToken
{

    public function getPattern()
    {
        return '/\{F#(\d+|current),P#(\d+|current),Y([+-]?\d+)\}/';
    }

    public function replace(Jmp $calculator, array $matches, $currentFilterId, array $questionnaires, $currentPartId, $year, array $years, ArrayCollection $alreadyUsedRules)
    {
        $filterId = $this->getId($matches[1], $currentFilterId);
        $partId = $this->getId($matches[2], $currentPartId);
        $yearShift = $matches[3];
        $year += $yearShift;

        // Only compute thing if in current years, to avoid infinite recursitivy in a very distant future
        if (in_array($year, $years)) {
            $value = $calculator->computeFlattenOneYearWithFormula($year, $years, $filterId, $questionnaires, $partId);
        } else {
            $value = null;
        }

        return is_null($value) ? 'NULL' : $value;
    }

    public function getStructure(array $matches, Parser $parser)
    {
        $year = (int) $matches[3];
        if ($year > 0) {
            $year = '+' . $year;
        }

        return [
            'type' => 'regressionFilterValue',
            'filter' => $this->getFilterName($matches[1], $parser),
            'part' => $this->getPartName($matches[2], $parser),
            'year' => (string) $year,
        ];
    }

}
