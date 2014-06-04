<?php

namespace Application\Service\Syntax;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Service\Calculator\Jmp;

/**
 * Replace {F#12,#P34,Y+0} with Filter regression value
 */
class RegressionFilterValue extends AbstractRegressionToken
{

    public function getPattern()
    {
        return '/\{F#(\d+|current),P#(\d+|current),Y([+-]?\d+)}/';
    }

    public function replace(Jmp $calculator, array $matches, $currentFilterId, array $questionnaires, $currentPartId, $year, array $years, ArrayCollection $alreadyUsedRules)
    {
        $filterId = $matches[1];
        $partId = $matches[2];
        $yearShift = $matches[3];
        $year += $yearShift;

        if ($filterId == 'current') {
            $filterId = $currentFilterId;
        }

        if ($partId == 'current') {
            $partId = $currentPartId;
        }

        // Only compute thing if in current years, to avoid infinite recursitivy in a very distant future
        if (in_array($year, $years)) {
            $value = $calculator->computeFlattenOneYearWithFormula($year, $years, $filterId, $questionnaires, $partId);
        } else {
            $value = null;
        }

        return is_null($value) ? 'NULL' : $value;
    }

}
