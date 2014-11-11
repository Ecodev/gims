<?php

namespace Application\Service\Calculator;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Model\Questionnaire;
use Application\Model\Filter;
use Application\Model\Part;
use Application\Model\Rule\Rule;

/**
 * Additional computing capabilities related to regressions
 */
class Calculator extends AbstractCalculator
{

    private $cacheComputeRegressionForAllYears = array();
    private $cacheComputeFlattenOneYearWithFormula = array();
    private $filterGeonameUsageRepository;

    /**
     * Returns the range of years for which we compute things
     * @return integer[]
     */
    public function getYears()
    {
        return range(1980, 2015); // if changed, modify in js/service/chart.js too
    }

    /**
     * Set the filterGeonameUsage repository
     * @param \Application\Repository\Rule\FilterGeonameUsageRepository $filterGeonameUsageRepository
     * @return self
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
     * @param \Application\Model\Filter $filter
     * @param \Application\Model\Questionnaire[] $questionnaires
     * @param \Application\Model\Part $part
     * @return array [[name => filterName, data => [year => flattenedRegression]]]]
     */
    public function computeFlattenAllYears(Filter $filter, array $questionnaires, Part $part)
    {
        $years = $this->getYears();
        $result = array();
        foreach ($years as $year) {
            $result[] = $this->computeFlattenOneYearWithFormula($year, $filter->getId(), $questionnaires, $part->getId());
        }

        return $result;
    }

    /**
     * Compute the flatten regression value for the given year with optional formulas
     * @param integer $year
     * @param integer $filterId
     * @param \Application\Model\Questionnaire[] $questionnaires
     * @param integer $partId
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadyUsedRules
     * @return null|float
     */
    public function computeFlattenOneYearWithFormula($year, $filterId, array $questionnaires, $partId, ArrayCollection $alreadyUsedRules = null)
    {
        if (!$alreadyUsedRules) {
            $alreadyUsedRules = new ArrayCollection();
        }

        $key = \Application\Utility::getPersistentCacheKey([$year, $filterId, $questionnaires, $partId, $alreadyUsedRules, $this->overriddenFilters]);
        if (array_key_exists($key, $this->cacheComputeFlattenOneYearWithFormula)) {
            return $this->cacheComputeFlattenOneYearWithFormula[$key];
        }

        // If the filter has a formula, returns its value
        if ($questionnaires) {
            $geonameId = reset($questionnaires)->getGeoname()->getId();
            $this->getCache()->record("F#$filterId,G#$geonameId,P#$partId");
            $filterGeonameUsage = $this->getFilterGeonameUsageRepository()->getFirst($geonameId, $filterId, $partId, $alreadyUsedRules);
            if ($filterGeonameUsage) {
                $this->getCache()->record($filterGeonameUsage->getCacheKey());
                $oneYearResult = $this->computeFormulaAfterRegression($filterGeonameUsage->getRule(), $year, $filterId, $questionnaires, $partId, $alreadyUsedRules);
                $this->cacheComputeFlattenOneYearWithFormula[$key] = $oneYearResult;

                _log()->debug(__METHOD__, array($filterId, $partId, $year, $oneYearResult));

                return $oneYearResult;
            }
        }

        // Otherwise fallback to normal computation
        $allRegressions = $this->computeRegressionForAllYears($filterId, $questionnaires, $partId);
        $oneYearResult = $this->computeFlattenOneYear($year, $allRegressions);
        $this->cacheComputeFlattenOneYearWithFormula[$key] = $oneYearResult;

        return $oneYearResult;
    }

    /**
     * Compute the flatten regression value for the given year
     * @param integer $year
     * @param array $regressions ['all' => [year => regresssion], 'min' => minRegression, 'max' => maxRegression]
     * @param array $usedYears [year] should be empty array for first call, then used for recursivity
     * @return null|float
     */
    public function computeFlattenOneYear($year, array $regressions, array $usedYears = array())
    {
        $allRegressions = $regressions['all'];
        $minRegression = $regressions['min'];
        $maxRegression = $regressions['max'];

        if (!array_key_exists($year, $allRegressions)) {
            return null;
        }

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
            $flattenYearEarlier = !in_array($yearEarlier, $usedYears) ? $this->computeFlattenOneYear($yearEarlier, $regressions, $usedYears) : null;

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
            $flattenYearLater = !in_array($yearLater, $usedYears) ? $this->computeFlattenOneYear($yearLater, $regressions, $usedYears) : null;

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
     * @param integer $filterId
     * @param array $questionnaires
     * @param integer $partId
     * @return array ['all' => [year => regresssion], 'min' => minRegression, 'max' => maxRegression]
     */
    private function computeRegressionForAllYears($filterId, array $questionnaires, $partId)
    {
        $key = \Application\Utility::getPersistentCacheKey([$filterId, $questionnaires, $partId, $this->overriddenFilters]);
        if (array_key_exists($key, $this->cacheComputeRegressionForAllYears)) {
            return $this->cacheComputeRegressionForAllYears[$key];
        }

        $allRegressions = array();
        $min = null;
        $max = null;
        foreach ($this->getYears() as $year) {
            $regression = $this->computeRegressionOneYear($year, $filterId, $questionnaires, $partId);
            $allRegressions[$year] = $regression;

            if (!is_null($regression)) {
                if (is_null($min) || $regression < $min) {
                    $min = $regression;
                }

                if (is_null($max) || $regression > $max) {
                    $max = $regression;
                }
            }
        }

        $result = [
            'all' => $allRegressions,
            'min' => $min,
            'max' => $max,
        ];

        $this->cacheComputeRegressionForAllYears[$key] = $result;

        return $result;
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
     * @return array [values => [], count => integer, years => [], surveys => [], minYear => integer, maxYear => integer, period => integer, slope => float, average => float]
     */
    public function computeFilterForAllQuestionnaires($filterId, array $questionnaires, $partId)
    {
        $result = array(
            'values' => array(),
            'count' => 0,
        );

        $years = array();
        $surveys = array();
        $yearsWithData = array();
        foreach ($questionnaires as $questionnaire) {

            $questionnaireData = $this->computeFilterForSingleQuestionnaire($filterId, $questionnaire, $partId);

            $questionnaireId = $questionnaire->getId();
            $years[$questionnaireId] = $questionnaireData['year'];
            $surveys[$questionnaireId] = $questionnaireData['code'];
            $result['values'][$questionnaireId] = $questionnaireData['value'];

            if (!is_null($questionnaireData['value'])) {
                $yearsWithData[] = $questionnaireData['year'];
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

        return $result;
    }

    /**
     * Compute a single questionnaire
     * @param integer $filterId
     * @param \Application\Model\Questionnaire $questionnaire
     * @param integer $partId
     * @return array
     */
    protected function computeFilterForSingleQuestionnaire($filterId, Questionnaire $questionnaire, $partId)
    {
        $questionnaireId = $questionnaire->getId();
        $key = 'computeFilterForAllQuestionnaires:' . \Application\Utility::getPersistentCacheKey([$filterId, $questionnaireId, $partId, $this->overriddenFilters]);

        if ($this->getCache()->hasItem($key)) {
            $result = $this->getCache()->getItem($key);

            return $result;
        }
        $this->getCache()->startComputing($key);

        $result = [];
        $result['year'] = $questionnaire->getSurvey()->getYear();
        $result['code'] = $questionnaire->getSurvey()->getCode();
        $result['value'] = $this->computeFilter($filterId, $questionnaireId, $partId, true);

        $this->getCache()->setItem($key, $result);

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
     * @param integer $currentFilterId
     * @param array $questionnaires
     * @param integer $currentPartId
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadyUsedRules
     * @return null|float
     */
    public function computeFormulaAfterRegression(Rule $rule, $year, $currentFilterId, array $questionnaires, $currentPartId, ArrayCollection $alreadyUsedRules = null)
    {
        if (!$alreadyUsedRules) {
            $alreadyUsedRules = new ArrayCollection();
        }
        $alreadyUsedRules->add($rule);
        $this->getCache()->record('rule:' . $rule->getId());

        $originalFormula = $rule->getFormula();
        $convertedFormula = $this->getParser()->convertAfterRegression($this, $originalFormula, $currentFilterId, $questionnaires, $currentPartId, $year, $alreadyUsedRules);
        $result = $this->getParser()->computeExcelFormula($convertedFormula);

        _log()->debug(__METHOD__, array($currentFilterId, $currentPartId, $year, $rule->getId(), $rule->getName(), $originalFormula, $convertedFormula, $result));

        return $result;
    }

}
