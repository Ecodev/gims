<?php

namespace Application\Service\Syntax;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Service\Calculator\Jmp;

/**
 * Replace {Y} with current year
 */
class RegressionYear extends AbstractRegressionToken
{

    public function getPattern()
    {
        return '/\{Y\}/';
    }

    public function replace(Jmp $calculator, array $matches, $currentFilterId, array $questionnaires, $currentPartId, $year, array $years, ArrayCollection $alreadyUsedRules)
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
