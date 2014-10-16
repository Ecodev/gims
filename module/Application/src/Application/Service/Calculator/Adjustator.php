<?php

namespace Application\Service\Calculator;

use Application\Model\Questionnaire;
use Application\Model\Geoname;
use Application\Model\Part;
use Application\Model\Filter;

/**
 * Class used to "adjust" a trend line over another one by overriding some values.
 * Those overriding values are a "best-guess" effort.
 */
class Adjustator
{

    /**
     * @var Aggregator
     */
    private $aggregator;

    /**
     * @var Filter
     */
    private $target;

    /**
     * @var Filter
     */
    private $reference;

    /**
     * @var Filter
     */
    private $overridable;

    /**
     * @var Geoname
     */
    private $geoname;

    /**
     * @var Part
     */
    private $part;

    public function setAggregator(Aggregator $aggregator)
    {
        $this->aggregator = $aggregator;
    }

    private function initObjects(Filter $target, Filter $reference, Filter $overridable, Geoname $geoname, Part $part)
    {
        $this->target = $target;
        $this->reference = $reference;
        $this->overridable = $overridable;
        $this->geoname = $geoname;
        $this->part = $part;
    }

    /**
     * Returns an array containing values that should be used to override filter,
     * in order to "move" $reference as close as possible to $target, by overriding $overridable
     * @param \Application\Model\Filter $target The filter to move close to
     * @param \Application\Model\Filter $reference The filter that will move
     * @param \Application\Model\Filter $overridable The filter that will be overridden in order to move $reference
     * @param Geoname $geoname
     * @param \Application\Model\Part $part
     * @return array
     */
    public function findOverriddenFilters(Filter $target, Filter $reference, Filter $overridable, Geoname $geoname, Part $part)
    {
        $this->initObjects($target, $reference, $overridable, $geoname, $part);
        $yearsWithValue = $this->getReferenceYearsWithValue();
        $overriddenFilters = [];

        if (!$yearsWithValue) {
            return $overriddenFilters;
        }

        $targetValues = $this->getTargetValues($yearsWithValue);

        // foreach years-target, find best value for reference
        foreach ($yearsWithValue as $questionnaireId => $year) {
            $overrideValue = $this->findBestOverrideValue($questionnaireId, $targetValues[$year]);
            $overriddenFilters[$questionnaireId][$this->overridable->getId()][$this->part->getId()] = $overrideValue;
        }

        return $overriddenFilters;
    }

    /**
     * Return an array containing original values before projection in order to allow difference computation
     * @param Filter $target
     * @param Filter $reference
     * @param Filter $overridable
     * @param Geoname $geoname
     * @param Part $part
     * @return array$
     */
    public function getOriginalOverrideValues(Filter $target, Filter $reference, Filter $overridable, Geoname $geoname, Part $part)
    {
        $this->initObjects($target, $reference, $overridable, $geoname, $part);
        $yearsWithValue = $this->getReferenceYearsWithValue();
        $originalValues = [];

        if (!$yearsWithValue) {
            return $originalValues;
        }

        $this->aggregator->getCalculator()->setOverriddenFilters(array());
        foreach ($yearsWithValue as $questionnaireId => $year) {
            $originalValue = $this->aggregator->getCalculator()->computeFilter($this->overridable->getId(), $questionnaireId, $this->part->getId());
            $originalValues[$questionnaireId][$this->overridable->getId()][$this->part->getId()] = $originalValue;
        }

        return $originalValues;
    }

    /**
     * Find all years with a questionnaire with a value for the reference filter
     * @return array [questionnaireId => year]
     */
    private function getReferenceYearsWithValue()
    {
        $questionnairesValues = $this->aggregator->computeFilterForAllQuestionnaires($this->reference->getId(), $this->geoname, $this->part->getId());

        // Remove questionnaires with null values
        foreach ($questionnairesValues['values'] as $questionnaireId => $value) {
            if (is_null($value)) {
                unset($questionnairesValues['years'][$questionnaireId]);
            }
        }

        return $questionnairesValues['years'];
    }

    /**
     * Find the target value for each year (of each questionnaire)
     * @param array $yearsWithValue
     * @return array [year => value]
     */
    private function getTargetValues(array $yearsWithValue)
    {
        $flattenValues = $this->aggregator->computeFlattenAllYears([$this->target], $this->geoname, $this->part);

        $targetValues = [];
        $i = 0;
        foreach ($this->aggregator->getCalculator()->getYears() as $year) {
            if (in_array($year, $yearsWithValue)) {
                $targetValues[$year] = $flattenValues[0]['data'][$i];
            }
            $i++;
        }

        return $targetValues;
    }

    /**
     * Try to find the best value to use as override in order to be closest to $targetValue.
     * The algorithm is dichotomy based, and inspired by the number-guessing game (where the other guy tells you "bigger"/"smaller")
     * @param integer $questionnaireId
     * @param float $targetValue
     * @return float
     */
    private function findBestOverrideValue($questionnaireId, $targetValue)
    {
        $this->aggregator->getCalculator()->setOverriddenFilters(array());
        $margin = 0.01 * $targetValue; // Give us a margin of +/-1% around the target
        $lowerLimit = 0;
        $currentValue = $this->aggregator->getCalculator()->computeFilter($this->reference->getId(), $questionnaireId, $this->part->getId());
        $overrideValue = $this->aggregator->getCalculator()->computeFilter($this->overridable->getId(), $questionnaireId, $this->part->getId());
        $higherLimit = $overrideValue * 4; // We assume that the it's usually less than 4 times bigger than current value

        $attempt = 0;
        while (($currentValue > $targetValue + $margin || $currentValue < $targetValue - $margin) && $attempt < 100 && $overrideValue != 0) {

            // If we are too high, try lower
            if ($currentValue > $targetValue + $margin) {
                $higherLimit = min($overrideValue, $higherLimit);
            }
            // If we are too low, try higher
            else {
                $lowerLimit = max($overrideValue, $lowerLimit);

                // If lower and higher limit are too close, that means that the maximum assumed
                // at the beginning was too low and we need to artificially raise the maximum
                if ($lowerLimit + $margin >= $higherLimit) {
                    $higherLimit = $higherLimit * 2;
                }
            }

            $overrideValue = ($lowerLimit + $higherLimit) / 2;

            // If the overriden value is almost zero, we force it to be exactly zero
            // to allow us to break the loop early, because we will never go lower than zero
            if (0 === bccomp($overrideValue, 0, 5)) {
                $overrideValue = 0;
            }

            $overriddenFilters = [$questionnaireId => [$this->overridable->getId() => [$this->part->getId() => $overrideValue]]];
            $this->aggregator->getCalculator()->setOverriddenFilters($overriddenFilters);
            $currentValue = $this->aggregator->getCalculator()->computeFilter($this->reference->getId(), $questionnaireId, $this->part->getId());
            $attempt++;
        }

        return $overrideValue;
    }

}
