<?php

namespace Application\Service\Calculator;

use Application\Model\Filter;
use Application\Model\Geoname;
use Application\Model\Part;

/**
 * Aggregate computing results by geonames. If the given Geoname has children, then the
 * it will aggregate those children to get the final result.
 *
 * Basically it is used to compute value for region of the world instead of countries.
 */
class Aggregator
{

    /**
     * @var Calculator
     */
    private $calculator;

    /**
     * Returns the calculator
     * @return \Application\Service\Calculator\Calculator
     */
    public function getCalculator()
    {
        return $this->calculator;
    }

    /**
     * Sets the calculator
     * @param \Application\Service\Calculator\Calculator $calculator
     */
    public function setCalculator(Calculator $calculator)
    {
        $this->calculator = $calculator;
    }

    /**
     * Same as Calculator::computeFlattenAllYears but aggregate geoname children
     * @param \Application\Model\Filter[] $filters
     * @param \Application\Model\Geoname $geoname
     * @param \Application\Model\Part $part
     * @return array
     */
    public function computeFlattenAllYears(array $filters, Geoname $geoname, Part $part)
    {
        $accumulators = [];
        foreach ($filters as $filter) {
            $accumulator = $this->computeFlattenAllYearsInternal($filter, $geoname, $part);
            $accumulators[$filter->getId()] = $accumulator;
        }

        $result = $this->accumulatorsToPercent($accumulators, $filters);

        return $result;
    }

    /**
     * Same as Calculator::computeFlattenAllYears but aggregate geoname children
     * @param \Application\Model\Filter $filter
     * @param \Application\Model\Geoname $geoname
     * @param \Application\Model\Part $part
     * @return array
     */
    private function computeFlattenAllYearsInternal(Filter $filter, Geoname $geoname, Part $part)
    {
        $questionnaires = $this->calculator->getQuestionnaireRepository()->getAllForComputing([$geoname]);
        $key = 'computeFlattenAllYearsInternal:' . \Application\Utility::getPersistentCacheKey([$filter->getId(), $geoname->getId(), $part->getId(), $this->calculator->getOverriddenFilters(), $questionnaires]);

        /* @var $cache \Application\Service\Calculator\Cache */
        $cache = \Application\Module::getServiceManager()->get('Calculator\Cache');
        if ($cache->hasItem($key)) {
            return $cache->getItem($key);
        }
        $cache->startComputing($key);
        $cache->record('geoname:' . $geoname->getId());

        // First, accumulate the parent
        $computed = $this->calculator->computeFlattenAllYears($filter, $questionnaires, $part);
        $accumulator = $this->computedToAccumulator($computed, $geoname, $part);

        // Then, accumulate all children
        foreach ($geoname->getChildren() as $child) {
            $childResult = $this->computeFlattenAllYearsInternal($filter, $child, $part);
            $accumulator = $this->accumulate($accumulator, $childResult);
        }

        $cache->setItem($key, $accumulator);

        return $accumulator;
    }

    /**
     * Transform the accumulated values into percentage to get the final aggregated result
     * @param array $accumulator
     * @return array final aggregated result
     */
    private function accumulatorsToPercent(array $accumulator, array $filters)
    {
        $filtersById = \Application\Utility::indexById($filters);
        foreach ($accumulator as $filterId => &$filter) {
            $filter['name'] = $filtersById[$filterId]->getName();
            $filter['id'] = $filterId;
            $filter['data'] = [];
            foreach ($filter['absoluteData'] as $i => $absoluteValue) {
                $population = $filter['population'][$i];

                if (is_null($absoluteValue) || !$population) {
                    $value = null;
                } else {
                    $value = $absoluteValue / $population;
                }

                $filter['data'][$i] = $value;
            }

            // Remove accumulated things, because we don't need them anymore
            unset($filter['absoluteData']);
            unset($filter['population']);
        }

        return array_values($accumulator);
    }

    /**
     * Convert data from computing to accumulator.
     * The structure of accumulator is similar to computing result, except it is absolute values,
     * and population is the sum of population for which we have a value.
     * @param array $computedValues
     * @param Geoname $geoname
     * @param Part $part
     * @return type
     */
    private function computedToAccumulator(array $computedValues, Geoname $geoname, Part $part)
    {
        $accumulator = [
            'absoluteData' => [],
            'population' => [],
        ];
        $years = $this->calculator->getYears();

        // Convert value and population
        foreach ($computedValues as $valueIndex => $value) {

            // Only count the value and its population if it is not null
            if (is_null($value)) {
                $population = null;
                $absoluteValue = null;
            } else {
                $year = $years[$valueIndex];
                $population = $this->calculator->getPopulationRepository()->getPopulationByGeoname($geoname, $part->getId(), $year);
                $absoluteValue = $value * $population;
            }
            $accumulator['absoluteData'][$valueIndex] = $absoluteValue;
            $accumulator['population'][$valueIndex] = $population;
        }

        return $accumulator;
    }

    /**
     * This will accumulate two accumulator together
     * @param array $accumulator1
     * @param array $accumulator2
     * @return array
     */
    private function accumulate(array $accumulator1, array $accumulator2)
    {
        // Cumulate value and population
        foreach ($accumulator2['absoluteData'] as $valueIndex => $value) {
            if (!isset($accumulator1['absoluteData'][$valueIndex])) {
                $accumulator1['absoluteData'][$valueIndex] = null;
                $accumulator1['population'][$valueIndex] = null;
            }

            if (!is_null($value)) {
                $accumulator1['absoluteData'][$valueIndex] += $accumulator2['absoluteData'][$valueIndex];
                $accumulator1['population'][$valueIndex] += $accumulator2['population'][$valueIndex];
            }
        }

        return $accumulator1;
    }

    /**
     * Same as Calculator::computeFilterForAllQuestionnaires but accept geoname for convenience
     * @param integer $filterId
     * @param \Application\Model\Geoname $geoname
     * @param integer $partId
     * @return array
     */
    public function computeFilterForAllQuestionnaires($filterId, Geoname $geoname, $partId)
    {
        $questionnaires = $this->calculator->getQuestionnaireRepository()->getAllForComputing([$geoname]);
        $result = $this->calculator->computeFilterForAllQuestionnaires($filterId, $questionnaires, $partId);

        return $result;
    }
}
