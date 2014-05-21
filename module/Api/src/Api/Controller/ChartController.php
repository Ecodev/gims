<?php

namespace Api\Controller;

use Application\View\Model\NumericJsonModel;
use Application\Model\FilterSet;
use Application\Model\Part;
use Application\Utility;
use Doctrine\Common\Collections\ArrayCollection;
use Application\Model\Question\NumericQuestion;
use Application\Model\Geoname;
use Application\Model\Answer;
use Application\Model\Questionnaire;
use Application\Model\Filter;
use Application\Model\Survey;
use Application\Model\User;
use Application\Model\UserSurvey;
use Application\Model\UserQuestionnaire;

class ChartController extends \Application\Controller\AbstractAngularActionController
{

    private $symbols = array(
        'circle',
        'diamond',
        'square',
        'triangle',
        'triangle-down'
    );
    private $startYear;
    private $endYear;

    /**
     * @var \Application\Service\Calculator\Jmp
     */
    private $calculator;

    /**
     * Get the JMP calculator shared instance
     * @return \Application\Service\Calculator\Jmp
     */
    private function getCalculator()
    {
        if (!$this->calculator) {
            $this->calculator = new \Application\Service\Calculator\Jmp();
            $this->calculator->setServiceLocator($this->getServiceLocator());
        }

        return $this->calculator;
    }

    /**
     * Return the entire structure to draw the chart
     * @param array $series
     * @param \Application\Model\Country $country
     * @param \Application\Model\Part $part
     * @return array
     */
    private function getChart(array $series, \Application\Model\Country $country = null, Part $part = null)
    {
        return array(
            'chart' => array(
                'zoomType' => 'xy',
                'height' => 600,
                'animation' => false,
            ),
            'title' => array(
                'text' => ($country ? $country->getName() : 'Unknown country') . ' - ' . ($part ? $part->getName() : 'Unkown part'),
            ),
            'subtitle' => array(
                'text' => 'Estimated proportion of the population',
            ),
            'xAxis' => array(
                'title' => array(
                    'enabled' => true,
                    'text' => 'Year',
                ),
                'labels' => array(
                    'step' => 1,
                    'format' => '{value}',
                ),
                'allowDecimals' => false,
            ),
            'yAxis' => array(
                'title' => array(
                    'enabled' => true,
                    'text' => 'Coverage (%)',
                ),
                'min' => 0,
                'max' => 100,
            ),
            'tooltip' => array('options' => array()),
            'credits' => array('enabled' => false),
            'plotOptions' => array(
                'line' => array(
                    'marker' => array(
                        'enabled' => false,
                    ),
                    'tooltip' => array(
                        'headerFormat' => '<span style="font-size: 10px">Estimate for {point.category}</span><br/>',
                        'pointFormat' => '<span style="color:{series.color}">{point.y}% {series.name}</span><br/>',
                        'footerFormat' => '<br><br><strong>Rules : </strong><br><br>{series.options.usages}</span><br/>',
                        'valueSuffix' => '%',
                    ),
                    'pointStart' => $this->startYear,
                    'dataLabels' => array(
                        'enabled' => false,
                    ),
                ),
                'scatter' => array(
                    'dataLabels' => array(
                        'enabled' => true,
                    ),
                    "tooltip" => array(
                        "headerFormat" => '',
                        "pointFormat" => '<b>{point.name}</b><br/>{point.y}% ({point.x})'
                    ),
                    'marker' => array(
                        'states' => array(
                            'select' => array(
                                'lineColor' => '#DDD',
                                'fillColor' => '#DDD',
                            ),
                        ),
                    ),
                ),
            ),
            'series' => $series,
        );
    }

    public function indexAction()
    {
        $country = $this->getEntityManager()->getRepository('Application\Model\Country')->findOneById($this->params()->fromQuery('country'));
        $filterSetsIds = array_filter(explode(',', $this->params()->fromQuery('filterSet')));

        $part = $this->getEntityManager()->getRepository('Application\Model\Part')->findOneById($this->params()->fromQuery('part'));
        $questionnaires = $this->getEntityManager()->getRepository('Application\Model\Questionnaire')->getByGeonameWithSurvey($country ? $country->getGeoname() : -1);

        $this->startYear = 1980;
        $this->endYear = 2012;

        $series = array();
        if (count($filterSetsIds) > 0) {

            // Compute adjusted series if we asked any
            $adjusted = $this->getAdjustedSeries($questionnaires, $part);
            $adjustedSeries = $adjusted['series'];

            $seriesWithIgnoredElements = array();
            foreach ($filterSetsIds as $filterSetId) {

                /* @var $filterSet \Application\Model\FilterSet */
                $filterSet = $this->getEntityManager()->getRepository('Application\Model\FilterSet')->findOneById($filterSetId);
                $filters = $filterSet->getFilters();

                // First get series of flatten regression lines with ignored values (if any)
                $seriesWithIgnoredElements = array_merge($seriesWithIgnoredElements, $this->computeIgnoredElements($filters, $questionnaires, $part));

                // Finally we compute "normal" series, and make it "light" if we have alternative series to highlight
                $alternativeSeries = array_merge($seriesWithIgnoredElements, $adjustedSeries);
                $normalSeries = $this->getSeries($filters, $questionnaires, $part, $alternativeSeries ? 33 : 100, $alternativeSeries ? 'ShortDash' : null, false);

                // Ensure that series are not added twice to series list
                foreach ($newSeries = array_merge($normalSeries, $alternativeSeries) as $newSerie) {
                    $same = false;
                    foreach ($series as $serie) {
                        if (count(@array_diff_assoc($serie, $newSerie)) == 0) {
                            $same = true;
                            break;
                        }
                    }
                    if (!$same) {
                        array_push($series, $newSerie);
                    }
                }
            }
        }

        $chart = $this->getChart($series, $country, $part);
        if (isset($adjusted['overriddenFilters']) && $adjusted['overriddenFilters']) {
            $chart['overriddenFilters'] = $adjusted['overriddenFilters'];
        }
        if (isset($adjusted['originalFilters']) && $adjusted['originalFilters']) {
            $chart['originalFilters'] = $adjusted['originalFilters'];
        }

        return new NumericJsonModel($chart);
    }

    /**
     * Always returns the same integer for the same name and incrementing: 0, 1, 2...
     * @staticvar array $keys
     * @param string $filterName
     * @return integer
     */
    private function getConstantKey($filterName)
    {
        static $keys = array();

        if (!array_key_exists($filterName, $keys)) {
            $keys[$filterName] = count($keys);
        }

        return $keys[$filterName];
    }

    /**
     * Returns all series for ignored questionnaires AND filters at the same time
     * @param \Application\Model\Filter[] $filters
     * @param array $questionnaires
     * @param \Application\Model\Part $part
     * @return array
     */
    protected function computeIgnoredElements($filters, array $questionnaires, Part $part)
    {
        $overriddenFilters = $this->getIgnoredElements($part);

        $series = array();
        if (count($overriddenFilters) > 0) {
            $questionnairesNotIgnored = array();
            foreach ($questionnaires as $questionnaire) {
                // if questionnaire is not in the ignored list, add to not ignored questionnaires list
                // or if questionnaire is in the ignored list but has filters, he's added to not ignored questionnaires list
                // (a questionnaire is considered ignored if he's in the list AND he has not filters. If he has filters, they are ignored, but not the questionnaire)
                if (!isset($overriddenFilters[$questionnaire->getId()]) || isset($overriddenFilters[$questionnaire->getId()]) && $overriddenFilters[$questionnaire->getId()] !== null
                ) {
                    $questionnairesNotIgnored[] = $questionnaire;
                }
            }

            $mySeries = $this->getSeries($filters, $questionnairesNotIgnored, $part, 100, null, true, ' (ignored elements)', $overriddenFilters);
            $series = array_merge($series, $mySeries);
        }

        return $series;
    }

    /**
     * Retrieve ignored elements and return un associative array where
     * key is questionnaire Id and value is a list of ignored filters
     * @param \Application\Model\Part $part
     * @return array
     */
    public function getIgnoredElements(Part $part)
    {
        $excludeStr = $this->params()->fromQuery('ignoredElements');
        $overriddenElements = $excludeStr ? explode(',', $excludeStr) : array();

        $overriddenFilters = array();
        foreach ($overriddenElements as $ignoredQuestionnaire) {
            @list($questionnaireId, $filters) = explode(':', $ignoredQuestionnaire);
            $filters = $filters ? explode('-', $filters) : $filters = array();
            if (count($filters) == 0) {
                $overriddenFilters[$questionnaireId] = null;
            } else {
                foreach ($filters as $filterId) {
                    $overriddenFilters[$questionnaireId][$filterId][$part->getId()] = null;
                }
            }
        }

        return $overriddenFilters;
    }

    /**
     * Get line and scatter series for the given filterSet and questionnaires
     * @param \Application\Model\Filter[] $filters
     * @param array $questionnaires
     * @param \Application\Model\Part $part
     * @param float $ratio
     * @param string $dashStyle
     * @param bool $isIgnored
     * @param string $suffix for serie name
     * @param array $overriddenFilters
     * @internal param array $colors
     * @return array
     */
    private function getSeries($filters, array $questionnaires, Part $part, $ratio, $dashStyle = null, $isIgnored = false, $suffix = null, array $overriddenFilters = array())
    {
        $this->getCalculator()->setOverriddenFilters($overriddenFilters);

        $lines = $this->getLinedSeries($filters, $questionnaires, $part, $ratio, $dashStyle, $isIgnored, $suffix);
        $scatters = $this->getScatteredSeries($filters, $questionnaires, $part, $ratio, $isIgnored, $suffix);

        $series = array_merge($lines, $scatters);

        return $series;
    }

    /**
     * Get lines series
     * @param \Application\Model\Filter[] $filters
     * @param array $questionnaires
     * @param Part $part
     * @param $ratio
     * @param null $dashStyle
     * @param $isIgnored
     * @param $suffix
     * @internal param array $ignoredFilters
     * @return array
     */
    private function getLinedSeries($filters, array $questionnaires, Part $part, $ratio, $dashStyle = null, $isIgnored, $suffix)
    {
        $series = array();

        /** @var \Application\Repository\Rule\FilterFilterRepository $filterRepository */
        $filterRepository = $this->getEntityManager()->getRepository('Application\Model\Filter');

        /** @var \Application\Model\Country $country */
        $country = $this->getEntityManager()->getRepository('Application\Model\Country')->findOneById($this->params()->fromQuery('country'));

        /** @var \Application\Repository\Rule\FilterGeonameUsageRepository $filterGeonameUsageRepo */
        $filterGeonameUsageRepo = $this->getEntityManager()->getRepository('Application\Model\Rule\FilterGeonameUsage');

        $lines = $this->getCalculator()->computeFlattenAllYears($this->startYear, $this->endYear, $filters, $questionnaires, $part);
        foreach ($lines as &$serie) {
            /** @var \Application\Model\Filter $filter */
            $filter = $filterRepository->findOneById($serie['id']);

            /** @var \Application\Repository\Rule\FilterGeonameUsage usages */
            $usages = $filterGeonameUsageRepo->getAllForGeonameAndFilter($country->getGeoname(), $filter, $part);

            $serie['color'] = $filter->getGenericColor($ratio);
            $serie['type'] = 'line';
            $serie['name'] .= $suffix;

            if ($isIgnored) {
                $serie['isIgnored'] = $isIgnored;
            }
            if ($dashStyle) {
                $serie['dashStyle'] = $dashStyle;
            }

            if (count($usages) > 0) {
                $serie['usages'] = implode(',<br/>', array_map(function ($u) {
                            return $u->getRule()->getName();
                        }, $usages));
            }

            foreach ($serie['data'] as &$d) {
                $d = \Application\Utility::decimalToRoundedPercent($d);
            }
            $series[] = $serie;
        }

        return $series;
    }

    /**
     * Get scatter series
     * @param \Application\Model\Filter[] $filters
     * @param array $questionnaires
     * @param Part $part
     * @param $ratio
     * @param $isIgnored
     * @param $suffix
     * @return array
     */
    private function getScatteredSeries($filters, array $questionnaires, Part $part, $ratio, $isIgnored, $suffix)
    {
        $series = array();

        // Then add scatter points which are each questionnaire values
        foreach ($filters as $filter) {
            $idFilter = $filter->getId();
            $data = $this->getCalculator()->computeFilterForAllQuestionnaires($filter->getId(), $questionnaires, $part->getId());
            $scatter = array(
                'type' => 'scatter',
                'color' => $filter->getGenericColor($ratio),
                'marker' => array('symbol' => $this->symbols[$this->getConstantKey($filter->getName()) % count($this->symbols)]),
                'name' => $filter->getName() . $suffix,
                'allowPointSelect' => false,
                // because we will use our own click handler
                'data' => array(),
            );
            if ($isIgnored) {
                $scatter['isIgnored'] = $isIgnored;
            }

            foreach ($data['values'] as $questionnaireId => $value) {

                if (!is_null($value)) {
                    $scatterData = array(
                        'name' => $data['surveys'][$questionnaireId],
                        'id' => $idFilter . ':' . $questionnaireId,
                        'questionnaire' => $questionnaireId,
                        'x' => $data['years'][$questionnaireId],
                        'y' => \Application\Utility::decimalToRoundedPercent($value),
                    );

                    /** @todo : old params denominations -> refresh */
                    // select the ignored values
                    $inQuestionnaires = false;
                    foreach ($questionnaires as $questionnaire) {
                        if ($questionnaire->getId() == $questionnaireId) {
                            $inQuestionnaires = true;
                            break;
                        }
                    }

                    if (!$inQuestionnaires) {
                        $scatterData['selected'] = 'true';
                    }
                    $scatter['data'][] = $scatterData;
                }
            }
            $series[] = $scatter;
        }

        return $series;
    }

    /**
     * Returns a list of filters for panel with values
     * @return NumericJsonModel
     */
    public function getPanelFiltersAction()
    {
        /** @var \Application\Model\Questionnaire $questionnaire */
        $questionnaire = $this->getEntityManager()->getRepository('Application\Model\Questionnaire')->findOneById($this->params()->fromQuery('questionnaire'));

        if ($questionnaire) {

            /** @var \Application\Model\Part $part */
            $part = $this->getEntityManager()->getRepository('Application\Model\Part')->findOneById($this->params()->fromQuery('part'));

            /** @var \Application\Model\Filter $filter */
            $filters = explode(',', $this->params()->fromQuery('filters'));
            $result = array('filters' => array());
            $resultWithoutIgnoredFilters = array('filters' => array());

            /** @var  \Application\Service\Hydrator $hydrator */
            $hydrator = new \Application\Service\Hydrator();

            if ($this->params()->fromQuery('getQuestionnaireUsages') === 'true') {
                $usages = $this->extractUsages($questionnaire->getQuestionnaireUsages(), null, $part, $hydrator);
                if ($usages) {
                    $result['usages'] = $usages;
                }
            }

            /**
             * This call recovers ignored questionnaires and filters.
             * In order to follow the logic : panel displays info to understand chart,
             * If the questionnaire requested is ignored all filters should be null (cause no value is used) on chart
             */
            $overriddenElements = $this->getIgnoredElements($part);
            foreach ($filters as $filterId) {
                $filter = $this->getEntityManager()->getRepository('Application\Model\Filter')->findOneById($filterId);
                $fields = array_merge(explode(',', $this->params()->fromQuery('fields')), array(
                    'questions',
                    'questions.survey'
                ));

                $tableController = new TableController();
                $tableController->setServiceLocator($this->getServiceLocator());

                $result['filters'][$filterId] = $tableController->computeWithChildren($questionnaire, $filter, array($part), 0, $fields, array(), true);
                $resultWithoutIgnoredFilters['filters'][$filterId] = $tableController->computeWithChildren($questionnaire, $filter, array($part), 0, $fields, $overriddenElements, true);

                foreach ($result['filters'][$filterId] as $i => &$flatFilter) {
                    $flatFilter = $this->addComputedValuesToFilters($flatFilter, $part, $resultWithoutIgnoredFilters['filters'][$filterId][$i]['values']);
                    $flatFilter = $this->addUsagesToFilters($flatFilter, $part, $questionnaire);
                    $flatFilter = $this->addQuestionsToFilters($flatFilter, $questionnaire);
                }
            }

            return new NumericJsonModel($result);
        } else {
            $this->getResponse()->setStatusCode(404);

            return new NumericJsonModel(array('message' => 'questionnaire not found'));
        }
    }

    /**
     * Add values computed in case we have ignored filters to given filter ($flatFilter)
     * @param $flatFilter
     * @param $part
     * @param $resultWithoutIgnoredFilters
     * @return array $flatFilter with modifications
     */
    private function addComputedValuesToFilters($flatFilter, $part, $resultWithoutIgnoredFilters)
    {
        // add computed values to filters
        if ($flatFilter['values'][0][$part->getName()] != $resultWithoutIgnoredFilters[0][$part->getName()]) {
            $flatFilter['valuesWithoutIgnored'] = $resultWithoutIgnoredFilters;
        }

        return $flatFilter;
    }

    /**
     * Add Usages to given filter ($flatFilter)
     * @param $flatFilter
     * @param $part
     * @param $questionnaire
     * @return array $flatFilter with modifications
     */
    private function addUsagesToFilters($flatFilter, $part, $questionnaire)
    {
        $flatFilter['usages'] = array();

        // add the usages to filters
        $fqus = $this->getEntityManager()->getRepository('Application\Model\Filter')->findOneById($flatFilter['filter']['id'])->getFilterQuestionnaireUsages();
        foreach ($fqus as $fqu) {
            if ($fqu->getPart() === $part && $fqu->getQuestionnaire() === $questionnaire) {
                $flatFilter['usages'][] = $fqu->getRule()->getName();
            }
        }

        $flatFilter['usages'] = implode(', ', $flatFilter['usages']);

        return $flatFilter;
    }

    /**
     * Add orignial denomations (questions labels) to given filter ($flatFilter)
     * @param $flatFilter
     * @param $questionnaire
     * @return array $flatFilter with modifications
     */
    private function addQuestionsToFilters($flatFilter, $questionnaire)
    {
        // replace the list of questions by a single question corresponding to current questionnaire
        if (isset($flatFilter['filter']['questions'])) {
            foreach ($flatFilter['filter']['questions'] as $question) {
                if ($question['survey']['id'] == $questionnaire->getSurvey()->getId()) {
                    $flatFilter['filter']['originalDenomination'] = $question['name'];
                    break;
                }
            }
            unset($flatFilter['filter']['questions']);
        }

        return $flatFilter;
    }

    protected function extractUsages($usages, $questionnaire = null, $part, $hydrator)
    {
        $extractedUsages = array();
        foreach ($usages as $usage) {
            if ($usage->getPart() === $part && (!$questionnaire || $questionnaire && $usage->getQuestionnaire() === $questionnaire)
            ) {
                $extractedUsage = $hydrator->extract($usage, array(
                    'part',
                    'rule',
                    'rule.name',
                ));
                $value = $this->getCalculator()->computeFormula($usage);
                $value = \Application\Utility::decimalToRoundedPercent($value);
                $extractedUsage['value'] = $value;
                $extractedUsages[] = $extractedUsage;
            }
        }

        return $extractedUsages;
    }

    /**
     * Return an array of series and overridden filters, if were asked to do it
     * @param array $questionnaires
     * @param \Application\Model\Part $part
     * @return array ['series' => series, 'overriddenFilters' => overriddenFilters]
     */
    private function getAdjustedSeries(array $questionnaires, Part $part)
    {
        $series = array();
        $overriddenFilters = array();
        $originalFilters = array();

        if ($this->params()->fromQuery('reference') && $this->params()->fromQuery('overridable') && $this->params()->fromQuery('target')) {

            $reference = $this->getEntityManager()->getRepository('Application\Model\Filter')->findOneById($this->params()->fromQuery('reference'));
            $overridable = $this->getEntityManager()->getRepository('Application\Model\Filter')->findOneById($this->params()->fromQuery('overridable'));

            @list($targetId, $includeIgnoredElements) = explode(':', $this->params()->fromQuery('target'));
            $target = $this->getEntityManager()->getRepository('Application\Model\Filter')->findOneById($targetId);

            if ($reference && $overridable && $target) {
                $adjustator = new \Application\Service\Calculator\Adjustator();
                $adjustator->setCalculator($this->getCalculator());

                $originalFilters = $adjustator->getOriginalOverrideValues($target, $reference, $overridable, $questionnaires, $part);

                if ($includeIgnoredElements) {
                    $ignoredElements = $this->getIgnoredElements($part);
                    $this->getCalculator()->setOverriddenFilters($ignoredElements);
                }

                $overriddenFilters = $adjustator->findOverriddenFilters($target, $reference, $overridable, $questionnaires, $part);
                $this->getCalculator()->setOverriddenFilters($overriddenFilters);

                $adjustedSeries = $this->getSeries([$reference], $questionnaires, $part, 100, null, false, ' (adjusted)', $overriddenFilters);
                $originalSeries = $this->getSeries([$reference], $questionnaires, $part, 33, 'ShortDash', false);
                $ancestorsLines = $this->getAncestorsLines($reference, $questionnaires, $part, $overriddenFilters);

                $series = array_merge($adjustedSeries, $originalSeries, $ancestorsLines);
            }
        }

        return array(
            'series' => $series,
            'overriddenFilters' => $overriddenFilters,
            'originalFilters' => $originalFilters,
        );
    }

    /**
     * Return parents trend lines of the projected filter
     * @param Filter $reference
     * @param array $questionnaires
     * @param Part $part
     * @param array $overriddenFilters
     * @return array
     */
    private function getAncestorsLines(Filter $reference, array $questionnaires, Part $part, array $overriddenFilters)
    {
        $topLevelFilters = $reference->getRootAncestors();
        $topLevelFiltersSeries = array();
        foreach ($topLevelFilters as $filter) {

            $topLevelFiltersSeries = array_merge($topLevelFiltersSeries, $this->getSeries([$filter], $questionnaires, $part, 100, null, false, ' (adjusted)', $overriddenFilters));
            $topLevelFiltersSeries = array_merge($topLevelFiltersSeries, $this->getSeries([$filter], $questionnaires, $part, 33, 'ShortDash', false));
        }

        return $topLevelFiltersSeries;
    }

}
