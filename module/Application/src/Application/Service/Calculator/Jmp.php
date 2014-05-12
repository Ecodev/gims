<?php

namespace Application\Service\Calculator;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Model\Part;
use Application\Model\FilterSet;
use Application\Model\Rule\Rule;

class Jmp extends Calculator
{

    private $cacheComputeFilterForAllQuestionnaires = array();
    private $cacheComputeRegressionForAllYears = array();
    private $cacheComputeFlattenOneYearWithFormula = array();
    private $filterGeonameUsageRepository;

    /**
     * Set the filterGeonameUsage repository
     * @param \Application\Repository\Rule\FilterGeonameUsageRepository $filterGeonameUsageRepository
     * @return \Application\Service\Calculator\Calculator
     */
    public function setFilterGeonameUsageRepository(\Application\Repository\Rule\FilterGeonameUsageRepository $filterGeonameUsageRepository)
    {
        $this->filterGeonameUsageRepository = $filterGeonameUsageRepository;

        return $this;
    }

    /**
     * Get the filterGeonameUsage repository
     * @return \Application\Repository\Rule\FilterGeonameUsageRepository
     */
    public function getFilterGeonameUsageRepository()
    {
        if (!$this->filterGeonameUsageRepository) {
            $this->filterGeonameUsageRepository = $this->getEntityManager()->getRepository('Application\Model\Rule\FilterGeonameUsage');
        }

        return $this->filterGeonameUsageRepository;
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
    public function computeFlattenAllYears($yearStart, $yearEnd, FilterSet $filterSet, array $questionnaires, Part $part, $ignoredFilters = array())
    {
        // @todo for sylvain. Property excluded filters is used in parent class. Check out @method computeFilterInternal
        $this->ignoredFilters = $ignoredFilters;

        $result = array();
        $years = range($yearStart, $yearEnd);
        foreach ($filterSet->getFilters() as $filter) {

            $data = array();
            foreach ($years as $year) {
                $data[] = $this->computeFlattenOneYearWithFormula($year, $years, $filter->getId(), $questionnaires, $part->getId());
            }

            $result[] = array(
                'name' => $filter->getName(),
                'id' => $filter->getId(),
                'data' => $data,
            );
        }

        return $result;
    }

    /**
     * Compute the flatten regression value for the given year with optional formulas
     * @param integer $year
     * @param array $years
     * @param integer $filterId
     * @param array $questionnaires
     * @param integer $partId
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadyUsedRules
     * @return null|float
     */
    protected function computeFlattenOneYearWithFormula($year, array $years, $filterId, array $questionnaires, $partId, ArrayCollection $alreadyUsedRules = null)
    {
        if (!$alreadyUsedRules) {
            $alreadyUsedRules = new ArrayCollection();
        }

        $key = \Application\Utility::getCacheKey([func_get_args(), $this->overridenFilters]);
        if (array_key_exists($key, $this->cacheComputeFlattenOneYearWithFormula)) {
            return $this->cacheComputeFlattenOneYearWithFormula[$key];
        }

        // If the filter has a formula, returns its value
        if ($questionnaires) {
            $filterGeonameUsage = $this->getFilterGeonameUsageRepository()->getFirst(reset($questionnaires)->getGeoname()->getId(), $filterId, $partId, $alreadyUsedRules);
            if ($filterGeonameUsage) {
                $oneYearResult = $this->computeFormulaFlatten($filterGeonameUsage->getRule(), $year, $years, $filterId, $questionnaires, $partId, $alreadyUsedRules);
                $this->cacheComputeFlattenOneYearWithFormula[$key] = $oneYearResult;

                _log()->debug(__METHOD__, array($filterId, $partId, $year, $oneYearResult));

                return $oneYearResult;
            }
        }

        // Otherwise fallback to normal computation
        $allRegressions = $this->computeRegressionForAllYears($years, $filterId, $questionnaires, $partId);
        $oneYearResult = $this->computeFlattenOneYear($year, $allRegressions);
        $this->cacheComputeFlattenOneYearWithFormula[$key] = $oneYearResult;

        _log()->debug(__METHOD__, array($filterId, $partId, $year, $oneYearResult));

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
            } elseif ($flattenYearEarlier === 1) {
                $result = 1;
            } elseif ($flattenYearEarlier === 0) {
                $result = 0;
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
            } elseif ($flattenYearLater === 1) {
                $result = 1;
            } elseif ($flattenYearLater === 0) {
                $result = 0;
            }
        }

        return $result;
    }

    /**
     * Returns regressions for each years specified
     * @param array $years
     * @param integer $filterId
     * @param array $questionnaires
     * @param integer $partId
     * @return array [year => regresssion]
     */
    private function computeRegressionForAllYears(array $years, $filterId, array $questionnaires, $partId)
    {
        $key = \Application\Utility::getCacheKey([func_get_args(), $this->overridenFilters]);
        if (array_key_exists($key, $this->cacheComputeRegressionForAllYears)) {
            return $this->cacheComputeRegressionForAllYears[$key];
        }

        $allRegressions = array();
        foreach ($years as $year) {
            $allRegressions[$year] = $this->computeRegressionOneYear($year, $filterId, $questionnaires, $partId);
        }

        $this->cacheComputeRegressionForAllYears[$key] = $allRegressions;

        return $allRegressions;
    }

    /**
     * Returns the regression for one year
     * @param integer $year
     * @param integer $filterId
     * @param array $questionnaires
     * @param integer $partId
     * @return null|float
     */
    public function computeRegressionOneYear($year, $filterId, array $questionnaires, $partId)
    {
        $d = $this->computeFilterForAllQuestionnaires($filterId, $questionnaires, $partId);

        if ($year == $d['maxYear'] + 6) {
            $result = $this->computeRegressionOneYear($year - 4, $filterId, $questionnaires, $partId);
        } elseif ($year == $d['minYear'] - 6) {
            $result = $this->computeRegressionOneYear($year + 4, $filterId, $questionnaires, $partId);
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
     * @param integer $filterId
     * @param array $questionnaires
     * @param integer $partId
     * @return array
     */
    public function computeFilterForAllQuestionnaires($filterId, array $questionnaires, $partId)
    {
        $key = \Application\Utility::getCacheKey([func_get_args(), $this->overridenFilters]);

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

            $computed = $this->computeFilter($filterId, $questionnaire->getId(), $partId, true);
            $result['values'][$questionnaire->getId()] = $computed;

            if (!is_null($computed)) {
                $yearsWithData[] = $year;
                $result['count'] ++;
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
     * @param integer $year
     * @param array $years
     * @param integer $currentFilterId
     * @param array $questionnaires
     * @param integer $currentPartId
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadyUsedRules
     * @return null|float
     */
    public function computeFormulaFlatten(Rule $rule, $year, array $years, $currentFilterId, array $questionnaires, $currentPartId, ArrayCollection $alreadyUsedRules = null)
    {
        if (!$alreadyUsedRules) {
            $alreadyUsedRules = new ArrayCollection();
        }
        $alreadyUsedRules->add($rule);

        $originalFormula = $rule->getFormula();

        // Replace {F#12,Q#all} with a list of Filter values for all questionnaires
        $convertedFormulas = \Application\Utility::pregReplaceUniqueCallback('/\{F#(\d+|current),Q#all}/', function($matches) use ($currentFilterId, $questionnaires, $currentPartId) {
                    $filterId = $matches[1];

                    if ($filterId == 'current') {
                        $filterId = $currentFilterId;
                    }

                    $data = $this->computeFilterForAllQuestionnaires($filterId, $questionnaires, $currentPartId);

                    $values = array();
                    foreach ($data['values'] as $v) {
                        if (!is_null($v)) {
                            $values[] = $v;
                        }
                    }

                    $values = '{' . implode(', ', $values) . '}';

                    return $values;
                }, $originalFormula);

        // Replace {F#12,#P34,Y+0} with Filter regression value
        $convertedFormulas = \Application\Utility::pregReplaceUniqueCallback('/\{F#(\d+|current),P#(\d+|current),Y([+-]?\d+)}/', function($matches) use ($year, $years, $currentFilterId, $questionnaires, $currentPartId) {
                    $filterId = $matches[1];
                    $partId = $matches[2];
                    $yearShift = $matches[3];
                    $year += $yearShift;

                    if ($filterId == 'current') {
                        $filterId = $currentFilterId;
                    }

                    if ($partId == 'current') {
                        $partId = $currentPartId;
                    }

                    // Only compute thing if in current years, to avoid infinite recursitivy in a very distant future
                    if (in_array($year, $years)) {
                        $value = $this->computeFlattenOneYearWithFormula($year, $years, $filterId, $questionnaires, $partId);
                    } else {
                        $value = null;
                    }

                    return is_null($value) ? 'NULL' : $value;
                }, $convertedFormulas);


        // Replace {Q#all,P#12} with cumulated population
        $convertedFormulas = \Application\Utility::pregReplaceUniqueCallback('/\{Q#all,P#(\d+|current)}/', function($matches) use ($questionnaires, $currentPartId, $year) {
                    $partId = $matches[1];

                    if ($partId == 'current') {
                        $partId = $currentPartId;
                    }

                    $population = 0;
                    foreach ($questionnaires as $questionnaire) {

                        $population += $this->getPopulationRepository()->getOneByGeoname($questionnaire->getGeoname(), $partId, $year, $questionnaire->getId())->getPopulation();
                    }

                    return $population;
                }, $convertedFormulas);


        // Replace {self} with computed value without this formula
        $convertedFormulas = \Application\Utility::pregReplaceUniqueCallback('/\{self\}/', function() use ($year, $years, $currentFilterId, $questionnaires, $currentPartId, $alreadyUsedRules) {


                    $value = $this->computeFlattenOneYearWithFormula($year, $years, $currentFilterId, $questionnaires, $currentPartId, $alreadyUsedRules);

                    return is_null($value) ? 'NULL' : $value;
                }, $convertedFormulas);

        // Replace {Y} with current year
        $convertedFormulas = \Application\Utility::pregReplaceUniqueCallback('/\{Y\}/', function() use ($year) {
                    return $year;
                }, $convertedFormulas);

        $result = \PHPExcel_Calculation::getInstance()->_calculateFormulaValue($convertedFormulas);

        // In some edge cases, it may happen that we get FALSE or empty double quotes as result,
        // we need to convert it to NULL, otherwise it will be converted to
        // 0 later, which is not correct. Eg: '=IF(FALSE, NULL, NULL)', or '=IF(FALSE,NULL,"")'
        if ($result === false || $result === '""') {
            $result = null;
        }

        _log()->debug(__METHOD__, array($currentFilterId, $currentPartId, $year, $rule->getId(), $rule->getName(), $originalFormula, $convertedFormulas, $result));

        return $result;
    }

}
