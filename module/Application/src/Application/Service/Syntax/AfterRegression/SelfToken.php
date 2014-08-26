<?php

namespace Application\Service\Syntax\AfterRegression;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Service\Calculator\Calculator;
use Application\Service\Syntax\Parser;

/**
 * Replace {self} with computed value without this formula
 */
class SelfToken extends AbstractToken
{

    public function getPattern()
    {
        return '/\{self\}/';
    }

    public function replace(Calculator $calculator, array $matches, $currentFilterId, array $questionnaires, $currentPartId, $year, array $years, ArrayCollection $alreadyUsedRules)
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
