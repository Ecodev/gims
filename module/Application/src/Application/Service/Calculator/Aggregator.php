<?php

namespace Application\Service\Calculator;

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
     * @param array $filters
     * @param \Application\Model\Geoname $geoname
     * @param \Application\Model\Part $part
     * @return array
     */
    public function computeFlattenAllYears(array $filters, Geoname $geoname, Part $part)
    {
        // First, accumulate the parent
        $questionnaires = $this->calculator->getQuestionnaireRepository()->getAllForComputing($geoname);
        $parentResult = $this->calculator->computeFlattenAllYears($filters, $questionnaires, $part);
        $accumulator = $this->accumulate([], $parentResult, $geoname, $part);

        // Then, accumulate all children
        foreach ($geoname->getChildren() as $child) {
            $childResult = $this->computeFlattenAllYears($filters, $child, $part);
            $accumulator = $this->accumulate($accumulator, $childResult, $child, $part);
        }

        $result = $this->accumulatorToPercent($accumulator);

        return $result;
    }

    /**
     * Transform the accumulated values into percentage to get the final aggregated result
     * @param array $accumulator
     * @return array final aggregated result
     */
    private function accumulatorToPercent(array $accumulator)
    {
        foreach ($accumulator as &$filter) {
            $filter['data'] = [];
            foreach ($filter['absoluteData'] as $i => $absoluteValue) {
                $population = $filter['population'][$i];

                if (is_null($absoluteValue) || is_null($population)) {
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

        return $accumulator;
    }

    /**
     * This will accumulate results from computing into $accumulator as absolute values
     * The structure of accumulator is similar to computing result, except it is absolute values,
     * and population is the sum of population for which we have a value.
     * @param array $accumulator
     * @param array $computedValues
     * @param \Application\Model\Geoname $geoname
     * @param \Application\Model\Part $part
     * @return array
     */
    private function accumulate(array $accumulator, array $computedValues, Geoname $geoname, Part $part)
    {
        $years = $this->calculator->getYears();
        foreach ($computedValues as $filterIndex => $filter) {
            if (!isset($accumulator[$filterIndex])) {
                $accumulator[$filterIndex] = [
                    'name' => $filter['name'],
                    'id' => $filter['id'],
                    'absoluteData' => [],
                ];
            }

            // Cumulate value and population
            foreach ($filter['data'] as $valueIndex => $value) {
                if (!isset($accumulator[$filterIndex]['absoluteData'][$valueIndex])) {
                    $accumulator[$filterIndex]['absoluteData'][$valueIndex] = null;
                    $accumulator[$filterIndex]['population'][$valueIndex] = null;
                }

                // Only count the value (and its population) if it is not null
                if (!is_null($value)) {
                    $year = $years[$valueIndex];
                    $population = $this->calculator->getPopulationRepository()->getPopulationByGeoname($geoname, $part->getId(), $year);
                    $absoluteValue = $value * $population;

                    $accumulator[$filterIndex]['population'][$valueIndex] += $population;
                    $accumulator[$filterIndex]['absoluteData'][$valueIndex] += $absoluteValue;
                }
            }
        }

        return $accumulator;
    }

    /**
     * Same as Calculator::computeFilterForAllQuestionnaires but aggregate geoname children
     * @param integer $filterId
     * @param \Application\Model\Geoname $geoname
     * @param integer $partId
     * @return array
     */
    public function computeFilterForAllQuestionnaires($filterId, Geoname $geoname, $partId)
    {
        $questionnaires = $this->calculator->getQuestionnaireRepository()->getAllForComputing($geoname);

        $parent = $this->calculator->computeFilterForAllQuestionnaires($filterId, $questionnaires, $partId);
        $result = [
            'values' => $parent['values'],
            'years' => $parent['years'],
            'surveys' => $parent['surveys'],
        ];

        // Aggregate all children
        foreach ($geoname->getChildren() as $child) {
            $childResult = $this->computeFilterForAllQuestionnaires($filterId, $child, $partId);
            $result = $this->aggregateQuestionnaires($result, $childResult);
        }

        return $result;
    }

    /**
     * Return aggregation of questionnaire values.
     * We only aggregate useful data for end-user, and not everything that is available.
     * @param array $data1
     * @param array $data2
     * @return array
     */
    private function aggregateQuestionnaires(array $data1, array $data2)
    {
        $result = [
            'values' => $data1['values'] + $data2['values'],
            'years' => $data1['years'] + $data2['years'],
            'surveys' => $data1['surveys'] + $data2['surveys'],
        ];

        return $result;
    }

}
