<?php

namespace Application\Service\Syntax\AfterRegression;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Service\Calculator\Calculator;
use Application\Service\Syntax\Parser;

/**
 * Replace {Q#all,P#12} with cumulated population
 */
class CumulatedPopulation extends AbstractToken
{

    public function getPattern()
    {
        return '/\{Q#all,P#(\d+|current)\}/';
    }

    public function replace(Calculator $calculator, array $matches, $currentFilterId, array $questionnaires, $currentPartId, $year, array $years, ArrayCollection $alreadyUsedRules)
    {
        $partId = $this->getId($matches[1], $currentPartId);

        $population = 0;
        foreach ($questionnaires as $questionnaire) {
            $population += $calculator->getPopulationRepository()->getOneByGeoname($questionnaire->getGeoname(), $partId, $year, $questionnaire->getId())->getPopulation();
        }

        return $population;
    }

    public function getStructure(array $matches, Parser $parser)
    {
        return [
            'type' => 'regressionCumulatedPopulation',
            'part' => $parser->getPartName($matches[1]),
        ];
    }

}
