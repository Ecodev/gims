<?php

namespace Application\Service\Calculator;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Model\Part;
use Application\Model\FilterSet;
use Application\Model\Filter;
use Application\Model\Rule\Rule;

class Jmp extends Calculator
{

    private $cacheComputeFilterForAllQuestionnaires = array();
    private $cacheComputeRegressionForAllYears = array();

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
    public function computeFlattenAllYears($yearStart, $yearEnd, FilterSet $filterSet, array $questionnaires, Part $part, $excludedFilters = array())
    {
        // @todo for sylvain. Property excluded filters is used in parent class. Check out @method computeFilterInternal
        $this->excludedFilters = $excludedFilters;

        // Enable hardcoded complementary computing for total parts if we have data for other parts
        if ($part->isTotal() && !$this->getQuestionnaireRepository()->isOnlyTotal($questionnaires)) {
            $parts = $this->getPartRepository()->getAllNonTotal();
        } else {
            $parts = array();
        }

        $result = array();
        $years = range($yearStart, $yearEnd);
        foreach ($filterSet->getFilters() as $filter) {

            $data = array();
            foreach ($years as $year) {
                $data[] = $this->computeFlattenOneYearWithFormula($year, $years, $filter, $questionnaires, $part, $parts);
            }

            $result[] = array(
                'name' => $filter->getName(),
                'data' => $data,
            );
        }

        return $result;
    }

    /**
     * Compute the flatten regression value for the given year with optional formulas
     * @param integer $year
     * @param array $years
     * @param \Application\Model\Filter $filter
     * @param array $questionnaires
     * @param \Application\Model\Part $part
     * @param array $parts
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadyUsedRules
     * @return null|float
     */
    public function computeFlattenOneYearWithFormula($year, array $years, Filter $filter, array $questionnaires, Part $part, array $parts, ArrayCollection $alreadyUsedRules = null)
    {
        if (!$alreadyUsedRules) {
            $alreadyUsedRules = new ArrayCollection();
        }

        // If the filter has a formula, returns its value
        $usages = $questionnaires ? reset($questionnaires)->getGeoname()->getFilterGeonameUsages() : array();
        foreach ($usages as $filterGeonameUsage) {
            $rule = $filterGeonameUsage->getRule();
            if (!$alreadyUsedRules->contains($rule) && $filterGeonameUsage->getFilter() === $filter && $filterGeonameUsage->getPart() === $part) {
                return $this->computeFormulaFlatten($rule, $year, $years, $filter, $questionnaires, $part, $parts, $alreadyUsedRules);
            }
        }

        // If we are computing the total (not a specific part), we will sum all parts to get it
        if ($parts) {
            $oneYearResult = null;
            $totalPopulation = null;
            foreach ($parts as $p) {
                $allRegressions = $this->computeRegressionForAllYears($years, $filter, $questionnaires, $p);
                $resultPart = $this->computeFlattenOneYear($year, $allRegressions);

                if (!is_null($resultPart)) {
                    $population = $this->getPopulationRepository()->getOneByGeoname(reset($questionnaires)->getGeoname(), $p, $year)->getPopulation();
                    $totalPopulation += $population;
                    $oneYearResult += $resultPart * $population;
                }
            }

            if ($totalPopulation) {
                $oneYearResult = $oneYearResult / $totalPopulation;
            }
        }
        // Otherwise fallback to normal computation
        else {
            $allRegressions = $this->computeRegressionForAllYears($years, $filter, $questionnaires, $part);
            $oneYearResult = $this->computeFlattenOneYear($year, $allRegressions);
        }

        return $oneYearResult;
    }

    /**
     * Compute the flatten regression value for the given year
     * @param integer $year
     * @param array $allRegressions [year => regression]
     * @param array $usedYears [year] should be empty array for first call, then used for recursivity
     * @return null|float
     */
    public function computeFlattenOneYear($year, array $allRegressions, array $usedYears = array())
    {
        if (!array_key_exists($year, $allRegressions)) {
            return null;
        }

        $nonNullRegressions = array_reduce($allRegressions, function($result, $regression) {
                    if (!is_null($regression)) {
                        $result [] = $regression;
                    }

                    return $result;
                }, array());

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
            $flattenYearLater = !in_array($yearLater, $usedYears) ? $this->computeFlattenOneYear($yearLater, $allRegressions, $usedYears) : null;

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

    /**
     * Returns regressions for each years specified
     * @param array $years
     * @param \Application\Model\Filter $filter
     * @param array $questionnaires
     * @param \Application\Model\Part $part
     * @return array [year => regresssion]
     */
    public function computeRegressionForAllYears(array $years, Filter $filter, array $questionnaires, Part $part)
    {
        $key = \Application\Utility::getCacheKey(func_get_args());
        if (array_key_exists($key, $this->cacheComputeRegressionForAllYears)) {
            return $this->cacheComputeRegressionForAllYears[$key];
        }

        $allRegressions = array();
        foreach ($years as $year) {
            $allRegressions[$year] = $this->computeRegressionOneYear($year, $filter, $questionnaires, $part);
        }

        $this->cacheComputeRegressionForAllYears[$key] = $allRegressions;

        return $allRegressions;
    }

    /**
     * Returns the regression for one year
     * @param integer $year
     * @param \Application\Model\Filter $filter
     * @param array $questionnaires
     * @param \Application\Model\Part $part
     * @return null|float
     */
    public function computeRegressionOneYear($year, Filter $filter, array $questionnaires, Part $part)
    {
        $d = $this->computeFilterForAllQuestionnaires($filter, $questionnaires, $part);

        if ($year == $d['maxYear'] + 6) {
            $result = $this->computeRegressionOneYear($year - 4, $filter, $questionnaires, $part);
        } elseif ($year == $d['minYear'] - 6) {
            $result = $this->computeRegressionOneYear($year + 4, $filter, $questionnaires, $part);
        } elseif ($year < $d['maxYear'] + 3 && $year > $d['minYear'] - 3 && $d['count'] > 1 && $d['period'] > 4) {
            $result = $this->ifNonZeroValue($d['values'], function() use ($year, $d) {
                        return \PHPExcel_Calculation_Statistical::FORECAST($year, $d['values'], $d['years']);
                    });
        } elseif ($year < $d['maxYear'] + 7 && $year > $d['minYear'] - 7 && ($d['count'] < 2 || $d['period'] < 5)) {
            $result = \PHPExcel_Calculation_Statistical::AVERAGE($d['values']);
        } elseif ($year > $d['minYear'] - 7 && $year < $d['minYear'] - 1) {
            $result = $this->ifNonZeroValue($d['values'], function() use ($d) {
                        return \PHPExcel_Calculation_Statistical::FORECAST($d['minYear'] - 2, $d['values'], $d['years']);
                    });
        } elseif ($year > $d['maxYear'] + 1 && $year < $d['maxYear'] + 7) {
            $result = $this->ifNonZeroValue($d['values'], function() use ($d) {
                        return \PHPExcel_Calculation_Statistical::FORECAST($d['maxYear'] + 2, $d['values'], $d['years']);
                    });
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
    public function computeFilterForAllQuestionnaires(Filter $filter, array $questionnaires, Part $part)
    {
        $key = \Application\Utility::getCacheKey(func_get_args());

        if (array_key_exists($key, $this->cacheComputeFilterForAllQuestionnaires)) {
            return $this->cacheComputeFilterForAllQuestionnaires[$key];
        }

        $result = array(
            'values' => array(),
            'count' => 0,
        );

        $years = array();
        $surveys = array();
        $yearsWithData = array();
        foreach ($questionnaires as $questionnaire) {

            $year = $questionnaire->getSurvey()->getYear();
            $years[$questionnaire->getId()] = $year;
            $surveys[$questionnaire->getId()] = $questionnaire->getSurvey()->getCode();

            $computed = $this->computeFilter($filter->getId(), $questionnaire->getId(), $part->getId(), true);
            $result['values'][$questionnaire->getId()] = $computed;

            if (!is_null($computed)) {
                $yearsWithData[] = $year;
                $result['count']++;
            }
        }

        $result['years'] = $years;
        $result['surveys'] = $surveys;
        $result['minYear'] = $yearsWithData ? min($yearsWithData) : null;
        $result['maxYear'] = $yearsWithData ? max($yearsWithData) : null;
        $result['period'] = $result['maxYear'] - $result['minYear'] ? : 1;

        $result['slope'] = $result['count'] < 2 ? null : $this->ifNonZeroValue($result['values'], function() use ($result, $years) {
                            return \PHPExcel_Calculation_Statistical::SLOPE($result['values'], $years);
                        });

        $result['average'] = $result['count'] ? \PHPExcel_Calculation_MathTrig::SUM($result['values']) / $result['count'] : null;

        $this->cacheComputeFilterForAllQuestionnaires[$key] = $result;

        return $result;
    }

    /**
     * PHPExcel divide by zero, so we need to wrap it to ensure that we have at least 1 non-zero value
     * @param array $data
     * @param Closure $phpExcelFunction
     * @return float
     */
    private function ifNonZeroValue(array $data, \Closure $phpExcelFunction)
    {
        foreach ($data as $d) {
            if ($d) {
                return @$phpExcelFunction();
            }
        }

        return 0;
    }

    /**
     * Compute the value of a formula based on GIMS syntax.
     * For details about syntax, @see \Application\Model\Rule\Rule
     * @param \Application\Model\Rule\Rule $rule
     * @param type $year
     * @param array $years
     * @param \Application\Model\Filter $currentFilter
     * @param array $questionnaires
     * @param \Application\Model\Part $part
     * @param array $parts
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadyUsedRules
     * @return null|float
     */
    public function computeFormulaFlatten(Rule $rule, $year, array $years, Filter $currentFilter, array $questionnaires, Part $part, array $parts, ArrayCollection $alreadyUsedRules = null)
    {
        if (!$alreadyUsedRules) {
            $alreadyUsedRules = new ArrayCollection();
        }
        $alreadyUsedRules->add($rule);

        $originalFormula = $rule->getFormula();

        // Replace {F#12,Q#all} with a list of Filter values for all questionnaires
        $convertedFormulas = preg_replace_callback('/\{F#(\d+|current),Q#all}/', function($matches) use ($currentFilter, $questionnaires, $part) {
                    $filterId = $matches[1];
                    $filter = $filterId == 'current' ? $currentFilter : $this->getFilterRepository()->findOneById($filterId);

                    $data = $this->computeFilterForAllQuestionnaires($filter, $questionnaires, $part);

                    $values = array();
                    foreach ($data['values'] as $v) {
                        if (!is_null($v)) {
                            $values[] = $v;
                        }
                    }

                    $values = '{' . implode(', ', $values) . '}';

                    return $values;
                }, $originalFormula);

        // Replace {F#12} with Filter regression value
        $convertedFormulas = preg_replace_callback('/\{F#(\d+|current)}/', function($matches) use ($year, $years, $currentFilter, $questionnaires, $part, $parts) {
                    $filterId = $matches[1];

                    $filter = $filterId == 'current' ? $currentFilter : $this->getFilterRepository()->findOneById($filterId);

                    $value = $this->computeFlattenOneYearWithFormula($year, $years, $filter, $questionnaires, $part, $parts);

                    return is_null($value) ? 'NULL' : $value;
                }, $convertedFormulas);

        // Replace {self} with computed value without this formula
        $convertedFormulas = preg_replace_callback('/\{self\}/', function() use ($year, $years, $currentFilter, $questionnaires, $part, $parts, $alreadyUsedRules) {


                    $value = $this->computeFlattenOneYearWithFormula($year, $years, $currentFilter, $questionnaires, $part, $parts, $alreadyUsedRules);

                    return is_null($value) ? 'NULL' : $value;
                }, $convertedFormulas);

        $result = \PHPExcel_Calculation::getInstance()->_calculateFormulaValue($convertedFormulas);

        // In some edge cases, it may happen that we get FALSE or empty double quotes as result,
        // we need to convert it to NULL, otherwise it will be converted to
        // 0 later, which is not correct. Eg: '=IF(FALSE, NULL, NULL)', or '=IF(FALSE,NULL,"")'
        if ($result === false || $result === '""') {
            $result = null;
        }

        _log()->debug(__FUNCTION__, array($currentFilter->getId(), $part->getId(), $rule->getId(), $rule->getName(), $originalFormula, $convertedFormulas, $result));
        return $result;
    }

}
