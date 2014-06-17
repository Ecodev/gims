<?php

namespace Application\Service\Syntax;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Service\Calculator\Jmp;

/**
 * Replace {self} with computed value without this formula
 */
class RegressionSelf extends AbstractRegressionToken
{

    public function getPattern()
    {
        return '/\{self\}/';
    }

    public function replace(Jmp $calculator, array $matches, $currentFilterId, array $questionnaires, $currentPartId, $year, array $years, ArrayCollection $alreadyUsedRules)
    {
        $value = $calculator->computeFlattenOneYearWithFormula($year, $years, $currentFilterId, $questionnaires, $currentPartId, $alreadyUsedRules);

        return is_null($value) ? 'NULL' : $value;
    }

    public function getStructure(array $matches, Parser $parser)
    {
        return [
            'type' => 'self',
        ];
    }

}
