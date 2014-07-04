<?php

namespace Application\Service\Syntax;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Service\Calculator\Calculator;
use Application\Model\Rule\AbstractQuestionnaireUsage;

/**
 * Replace {F#12,P#34,Y+0} with Filter regression value
 */
class FilterValueAfterRegression extends AbstractBasicToken
{

    public function getPattern()
    {
        return '/\{F#(\d+|current),P#(\d+|current),Y([+-]?\d+)\}/';
    }

    public function replace(Calculator $calculator, array $matches, AbstractQuestionnaireUsage $usage, ArrayCollection $alreadyUsedFormulas, $useSecondLevelRules)
    {
        $filterId = $this->getFilterId($matches[1], $usage);
        $partId = $this->getPartId($matches[2], $usage);
        $yearOffset = $matches[3];
        $year = $usage->getQuestionnaire()->getSurvey()->getYear() + $yearOffset;

        $years = range(1980, 2012);
        $questionnaires = $calculator->getQuestionnaireRepository()->getAllForComputing($usage->getQuestionnaire()->getGeoname());

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
            'color' => $this->getFilterColor($matches[1], $parser),
        ];
    }

}
