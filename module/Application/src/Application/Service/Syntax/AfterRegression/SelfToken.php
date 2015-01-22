<?php

namespace Application\Service\Syntax\AfterRegression;

use Application\Service\Calculator\Calculator;
use Application\Service\Syntax\Parser;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Replace {self} with computed value without this formula
 */
class SelfToken extends AbstractToken implements \Application\Service\Syntax\BothContextInterface
{

    public function getPattern()
    {
        return '/\{self\}/';
    }

    public function replace(Calculator $calculator, array $matches, $currentFilterId, array $questionnaires, $currentPartId, $year, ArrayCollection $alreadyUsedRules)
    {
        $value = $calculator->computeFlattenOneYearWithFormula($year, $currentFilterId, $questionnaires, $currentPartId, $alreadyUsedRules);

        return is_null($value) ? 'NULL' : $value;
    }

    public function getStructure(array $matches, Parser $parser)
    {
        return [
            'type' => 'self',
        ];
    }

}
