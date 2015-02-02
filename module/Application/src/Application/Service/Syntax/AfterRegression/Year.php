<?php

namespace Application\Service\Syntax\AfterRegression;

use Application\Service\Calculator\Calculator;
use Application\Service\Syntax\Parser;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Replace {Y} with current year
 */
class Year extends AbstractToken
{

    public function getPattern()
    {
        return '/\{Y\}/';
    }

    public function replace(Calculator $calculator, array $matches, $currentFilterId, array $questionnaires, $currentPartId, $year, ArrayCollection $alreadyUsedRules)
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
