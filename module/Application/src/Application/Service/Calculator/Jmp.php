<?php

namespace Application\Service\Calculator;

use Application\Model\Questionnaire;
use Application\Model\Part;
use Application\Model\FilterSet;
use Application\Model\Filter;

class Jmp extends Calculator
{

    private $cacheComputeFilterForAllQuestionnaires = array();
    private $populationRepository;

    /**
     * Set the population repository
     * @param \Application\Repository\PopulationRepository $populationRepository
     * @return \Application\Service\Calculator\Jmp
     */
    public function setPopulationRepository(\Application\Repository\PopulationRepository $populationRepository)
    {
        $this->populationRepository = $populationRepository;

        return $this;
    }

    /**
     *
     * @return \Application\Repository\PopulationRepository
     */
    public function getPopulationRepository()
    {
        if (!$this->populationRepository) {
            $this->populationRepository = $this->getEntityManager()->getRepository('Application\Model\Population');
        }

        return $this->populationRepository;
    }

    /**
     * Returns an array of all filter data, which includes name and year-regression pairs
     * This is the highest level of computation, the "main" computation method.
     * @param integer $yearStart
     * @param integer $yearEnd
     * @param \Application\Model\FilterSet $filterSet
     * @param array $questionnaires
     * @param \Application\Model\Part $part
     * @return array [[name => filterName, data => [year => flattenedRegression]]]]
     */
    public function computeFlatten($yearStart, $yearEnd, FilterSet $filterSet, $questionnaires, Part $part = null)
    {
        $result = array();
        $years = range($yearStart, $yearEnd);
        foreach ($filterSet->getFilters() as $filter) {


            $allRegressions = array();
            foreach ($years as $year) {
                $allRegressions[$year] = $this->computeRegression($year, $filter, $questionnaires, $part);
            }

            $d = array();
            foreach ($years as $year) {
                $d[] = $this->computeFlattenOneYear($year, $allRegressions);
            }

            $result[] = array(
                'name' => $filter->getName(),
                'data' => $d,
            );
        }

        return $result;
    }

    /**
     * Compute the flatten regression value for the given year
     * @param integer $year
     * @param array $allRegressions [year => regression]
     * @param array $usedYears [year] should be empty array for first call, then used for recursivity
     * @return null|int
     */
    public function computeFlattenOneYear($year, array $allRegressions, array $usedYears = array())
    {
        if (!array_key_exists($year, $allRegressions)) {
            return null;
        }

        $nonNullRegressions = array_filter($allRegressions, function($regression) {
                    return !is_null($regression);
                });
        $minRegression = $nonNullRegressions ? min($nonNullRegressions) : null;
        $maxRegression = $nonNullRegressions ? max($nonNullRegressions) : null;
        $regression = $allRegressions[$year];

        array_push($usedYears, $year);

        // If regression value exists, make sure it's within our limits and returns it
        $result = null;
        if (!is_null($regression)) {
            if ($regression < 0) {
                $result = 0;
            } elseif ($regression > 1) {
                $result = 1;
            } else {
                $result = $regression;
            }
        }


        if (is_null($result)) {
            $yearEarlier = $year - 1;
            $flattenYearEarlier = !in_array($yearEarlier, $usedYears) ? $this->computeFlattenOneYear($yearEarlier, $allRegressions, $usedYears) : null;

            if ($flattenYearEarlier === $minRegression && $flattenYearEarlier < 0.05) {
                $result = $flattenYearEarlier;
            } elseif ($flattenYearEarlier === $maxRegression && $flattenYearEarlier < 0.05) {
                $result = $flattenYearEarlier;
            } elseif ($flattenYearEarlier === $maxRegression && $flattenYearEarlier > 0.95) {
                $result = $flattenYearEarlier;
            } elseif ($flattenYearEarlier === $minRegression && $flattenYearEarlier > 0.95) {
                $result = $flattenYearEarlier;
            }
        }

        if (is_null($result)) {
            $yearLater = $year + 1;
            $flattenYearLater = !in_array($yearEarlier, $usedYears) ? $this->computeFlattenOneYear($yearLater, $allRegressions, $usedYears) : null;

            if ($flattenYearLater === $minRegression && $flattenYearLater < 0.05) {
                $result = $flattenYearLater;
            } elseif ($flattenYearLater === $maxRegression && $flattenYearLater < 0.05) {
                $result = $flattenYearLater;
            } elseif ($flattenYearLater === $maxRegression && $flattenYearLater > 0.95) {
                $result = $flattenYearLater;
            } elseif ($flattenYearLater === $minRegression && $flattenYearLater > 0.95) {
                $result = $flattenYearLater;
            }
        }

        return $result;
    }

    public function computeRegression($year, Filter $filter, $questionnaires, Part $part = null)
    {
        $d = $this->computeFilterForAllQuestionnaires($filter, $questionnaires, $part);

        if ($year == $d['maxYear'] + 6) {
            $result = $this->computeRegression($year - 4, $filter, $questionnaires, $part);
        } elseif ($year == $d['minYear'] - 6) {
            $result = $this->computeRegression($year + 4, $filter, $questionnaires, $part);
        } elseif ($year < $d['maxYear'] + 3 && $year > $d['minYear'] - 3 && $d['count'] > 1 && $d['period'] > 4) {
            $result = \PHPExcel_Calculation_Statistical::FORECAST($year, $d['values%'], $d['years']);
        } elseif ($year < $d['maxYear'] + 7 && $year > $d['minYear'] - 7 && ($d['count'] < 2 || $d['period'] < 5)) {
            $result = \PHPExcel_Calculation_Statistical::AVERAGE($d['values%']);
        } elseif ($year > $d['minYear'] - 7 && $year < $d['minYear'] - 1) {
            $result = \PHPExcel_Calculation_Statistical::FORECAST($year - 2, $d['values%'], $d['years']);
        } elseif ($year > $d['maxYear'] + 1 && $year < $d['maxYear'] + 7) {
            $result = \PHPExcel_Calculation_Statistical::FORECAST($year + 2, $d['values%'], $d['years']);
        } else {
            $result = null;
        }

        return $result;
    }

    /**
     * Implement computing on filter level, as seen on tab "GraphData_W"
     * @param \Application\Model\Filter $filter
     * @param array $questionnaires
     * @param \Application\Model\Part $part
     * @return array
     */
    public function computeFilterForAllQuestionnaires(Filter $filter, $questionnaires, Part $part = null)
    {
        $key = null;
        foreach ($questionnaires as $questionnaire) {
            $key .= spl_object_hash($questionnaire);
        }
        $key .= spl_object_hash($filter) . ($part ? spl_object_hash($part) : null);

        if (array_key_exists($key, $this->cacheComputeFilterForAllQuestionnaires)) {
            return $this->cacheComputeFilterForAllQuestionnaires[$key];
        }

        $result = array(
            'values' => array(),
            'values%' => array(),
            'count' => 0,
        );
        $rules = $filter->getFilterRules();
        $totalPopulation = 0;
        $years = array();
        $yearsWithData = array();
        foreach ($questionnaires as $questionnaire) {

            // skip this questionnaire, if an exclude rule exists for him
            $skipQuestionnaire = false;
            foreach ($rules as $rule) {
                if ($rule->getRule() instanceof \Application\Model\Rule\Exclude && $rule->getQuestionnaire() == $questionnaire && $rule->getPart() == $part) {
                    $skipQuestionnaire = true;
                    break;
                }
            }

            if ($skipQuestionnaire) {
                continue;
            }

            $year = $questionnaire->getSurvey()->getYear();
            $years[] = $year;

            $computed = $this->computeFilter($filter, $questionnaire, $part);
            if (is_null($computed)) {

                $result['values'][$questionnaire->getSurvey()->getCode()] = null;
                $result['values%'][$questionnaire->getSurvey()->getCode()] = null;
                continue;
            }

            $yearsWithData[] = $year;

            $population = $this->getPopulationRepository()->getOneByQuestionnaire($questionnaire, $part);
            $totalPopulation += $population->getPopulation();
            $result['count']++;

            $result['values'][$questionnaire->getSurvey()->getCode()] = $computed;
            $result['values%'][$questionnaire->getSurvey()->getCode()] = $computed / $population->getPopulation();
        }

        $result['years'] = $years;
        $result['minYear'] = $yearsWithData ? min($yearsWithData) : null;
        $result['maxYear'] = $yearsWithData ? max($yearsWithData) : null;
        $result['period'] = $result['maxYear'] - $result['minYear'] ? : 1;

        $result['slope'] = $result['count'] < 2 ? null : \PHPExcel_Calculation_Statistical::SLOPE($result['values'], $years);
        $result['slope%'] = $result['count'] < 2 ? null : \PHPExcel_Calculation_Statistical::SLOPE($result['values%'], $years);

        $result['average'] = $result['count'] ? \PHPExcel_Calculation_MathTrig::SUM($result['values']) / $result['count'] : null;
        $result['average%'] = $result['count'] ? \PHPExcel_Calculation_MathTrig::SUM($result['values%']) / $result['count'] : null;
        $result['population'] = $totalPopulation;


        $this->cacheComputeFilterForAllQuestionnaires[$key] = $result;

        return $result;
    }

}

