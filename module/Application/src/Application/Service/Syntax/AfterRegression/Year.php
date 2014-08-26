<?php

namespace Application\Service\Syntax\AfterRegression;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Service\Calculator\Calculator;
use Application\Service\Syntax\Parser;

/**
 * Replace {Y} with current year
 */
class Year extends AbstractToken
{

    public function getPattern()
    {
        return '/\{Y\}/';
    }

    public function replace(Calculator $calculator, array $matches, $currentFilterId, array $questionnaires, $currentPartId, $year, array $years, ArrayCollection $alreadyUsedRules)
    {
        return $year;
    }

    public function getStructure(array $matches, Parser $parser)
    {
        return [
            'type' => 'regressionYear',
        ];
    }

}
