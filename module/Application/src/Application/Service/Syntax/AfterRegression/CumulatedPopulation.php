<?php

namespace Application\Service\Syntax\AfterRegression;

use Application\Service\Calculator\Calculator;
use Application\Service\Syntax\Parser;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Replace {Q#all,P#12} with cumulated population
 */
class CumulatedPopulation extends AbstractToken
{

    public function getPattern()
    {
        return '/\{Q#all,P#(\d+|current)\}/';
    }

    public function replace(Calculator $calculator, array $matches, $currentFilterId, array $questionnaires, $currentPartId, $year, ArrayCollection $alreadyUsedRules)
    {
        $partId = $this->getId($matches[1], $currentPartId);

        $cache = \Application\Module::getServiceManager()->get('Calculator\Cache');
        $population = 0;
        foreach ($questionnaires as $questionnaire) {
            $cache->record('questionnaire:' . $questionnaire->getId());
            $population += $calculator->getPopulationRepository()->getPopulationByGeoname($questionnaire->getGeoname(), $partId, $year, $questionnaire->getId());
        }

        return $population;
    }

    public function getStructure(array $matches, Parser $parser)
    {
        return [
            'type' => 'regressionCumulatedPopulation',
            'part' => [
                'id' => $matches[1],
                'name' => $parser->getPartName($matches[1]),
            ],
        ];
    }
}
