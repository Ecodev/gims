<?php

namespace Application\Service\Calculator;

use Application\Model\Questionnaire;
use Application\Model\Part;
use Application\Model\Filter;
use Application\Model\CategoryFilterComponent;

class Jmp extends Calculator
{

    private $cacheComputeCategoryFilterComponentForAllQuestionnaires = array();

    public function computeFlatten($yearStart, $yearEnd, Filter $filter, $questionnaires, Part $part = null)
    {
        $result = array();
        $years = range($yearStart, $yearEnd);
        foreach ($filter->getCategoryFilterComponents() as $filterComponent) {


            $allRegressions = array();
            foreach ($years as $year) {
                $allRegressions[$year] = $this->computeRegression($year, $filterComponent, $questionnaires, $part);
            }

            $d = array();
            foreach ($years as $year) {
                $d[] = $this->computeFlattenCategoryFilterComponent($year, $allRegressions);
            }

            $result[] = array(
                'name' => $filterComponent->getName(),
                'data' => $d,
            );
        }

        return $result;
    }

    protected function computeFlattenCategoryFilterComponent($year, $allRegressions, array $usedYears = array())
    {
        if (!array_key_exists($year, $allRegressions))
            return null;

        $regression = $allRegressions[$year];
        $minRegression = min($allRegressions);
        $maxRegression = max($allRegressions);

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
            $flattenYearEarlier = !in_array($yearEarlier, $usedYears) ? $this->computeFlattenCategoryFilterComponent($yearEarlier, $allRegressions, $usedYears) : null;

            if ($flattenYearEarlier === $minRegression && $flattenYearEarlier < 0) {
                $result = 0;
            } elseif ($flattenYearEarlier === $minRegression && $flattenYearEarlier < 0.05) {
                $result = $flattenYearEarlier;
            } elseif ($flattenYearEarlier === $maxRegression && $flattenYearEarlier < 0.05) {
                $result = $flattenYearEarlier;
            } elseif ($flattenYearEarlier === $maxRegression && $flattenYearEarlier > 1) {
                $result = 1;
            } elseif ($flattenYearEarlier === $maxRegression && $flattenYearEarlier > 0.95) {
                $result = $flattenYearEarlier;
            } elseif ($flattenYearEarlier === $minRegression && $flattenYearEarlier > 0.95) {
                $result = $flattenYearEarlier;
            } elseif ($flattenYearEarlier === 1) {
                $result = 1;
            } elseif ($flattenYearEarlier === 0) {
                $result = 0;
            }
        }

        if (is_null($result)) {
            $yearLater = $year + 1;
            $flattenYearLater = !in_array($yearEarlier, $usedYears) ? $this->computeFlattenCategoryFilterComponent($yearLater, $allRegressions, $usedYears) : null;

            if ($flattenYearLater == $minRegression && $flattenYearLater < 0) {
                $result = 0;
            } elseif ($flattenYearLater === $minRegression && $flattenYearLater < 0.05) {
                $result = $flattenYearLater;
            } elseif ($flattenYearLater === $maxRegression && $flattenYearLater < 0.05) {
                $result = $flattenYearLater;
            } elseif ($flattenYearLater === $maxRegression && $flattenYearLater > 1) {
                $result = 1;
            } elseif ($flattenYearLater === $maxRegression && $flattenYearLater > 0.95) {
                $result = $flattenYearLater;
            } elseif ($flattenYearLater === $minRegression && $flattenYearLater > 0.95) {
                $result = $flattenYearLater;
            } elseif ($flattenYearLater === 1) {
                $result = 1;
            } elseif ($flattenYearLater === 0) {
                $result = 0;
            }
        }

        return $result;
    }

    public function computeRegression($year, CategoryFilterComponent $filterComponent, $questionnaires, Part $part = null)
    {
        $d = $this->computeCategoryFilterComponentForAllQuestionnaires($filterComponent, $questionnaires, $part);

        if ($year == $d['maxYear'] + 6) {
            $result = $this->computeRegression($year - 4, $filterComponent, $questionnaires, $part);
        } elseif ($year == $d['minYear'] - 6) {
            $result = $this->computeRegression($year + 4, $filterComponent, $questionnaires, $part);
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
     * @param type $questionnaires
     * @param \Application\Model\Part $part
     * @return array
     */
    public function computeCategoryFilterComponentForAllQuestionnaires(CategoryFilterComponent $filterComponent, $questionnaires, Part $part = null)
    {
        $key = null;
        foreach ($questionnaires as $questionnaire) {
            $key .= spl_object_hash($questionnaire);
        }
        $key .= spl_object_hash($filterComponent) . ($part ? spl_object_hash($part) : null);

        if (array_key_exists($key, $this->cacheComputeCategoryFilterComponentForAllQuestionnaires)) {
            return $this->cacheComputeCategoryFilterComponentForAllQuestionnaires[$key];
        }

        $result = array(
            'values' => array(),
            'values%' => array(),
            'count' => 0,
        );

        $totalPopulation = 0;
        $years = array();
        $yearsWithData = array();
        foreach ($questionnaires as $questionnaire) {
            $year = $questionnaire->getSurvey()->getYear();
            $years[] = $year;

            $computed = $this->computeCategoryFilterComponent($filterComponent, $questionnaire, $part);
            if (is_null($computed)) {

                $result['values'][$questionnaire->getSurvey()->getCode()] = null;
                $result['values%'][$questionnaire->getSurvey()->getCode()] = null;
                continue;
            }

            $yearsWithData[] = $year;

            $population = $this->getEntityManager()->getRepository('Application\Model\Population')->getOneByQuestionnaire($questionnaire, $part);
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


        $this->cacheComputeCategoryFilterComponentForAllQuestionnaires[$key] = $result;

        return $result;
    }

}

