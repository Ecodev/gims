<?php

namespace Api\Controller;

use Application\View\Model\NumericJsonModel;
use Application\Model\Part;
use Application\Model\Geoname;
use Application\Utility;
use Application\Model\Filter;

class ChartController extends \Application\Controller\AbstractAngularActionController
{

    private $symbols = array(
        'circle',
        'diamond',
        'square',
        'triangle',
        'triangle-down'
    );
    private $startYear = 1980;
    private $endYear = 2012;

    /**
     * @var \Application\Service\Calculator\Calculator
     */
    private $calculator;

    /**
     * Get the JMP calculator shared instance
     * @return \Application\Service\Calculator\Calculator
     */
    private function getCalculator()
    {
        if (!$this->calculator) {
            $this->calculator = new \Application\Service\Calculator\Calculator();
            $this->calculator->setServiceLocator($this->getServiceLocator());
        }

        return $this->calculator;
    }

    /**
     * Return the entire structure to draw the chart
     * @param array $series
     * @param array $geonames
     * @param \Application\Model\Part $part
     * @return array
     */
    private function getChart(array $series, array $geonames = null, Part $part = null)
    {
        $geonameNames = join(', ', array_map(function($g) {
                    return $g->getName();
                }, $geonames));

        return array(
            'chart' => array(
                'zoomType' => 'xy',
                'height' => 600,
                'animation' => false,
            ),
            'title' => array(
                'text' => $geonameNames . ' - ' . ($part ? $part->getName() : 'Unkown part'),
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
                        "pointFormat" => '<b>{point.name}</b> ({point.x})<br/><span style="color:{series.color}">{point.y}% {series.name}</span>'
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
        $geonameIds = array_filter(explode(',', $this->params()->fromQuery('geonames')));
        $filtersIds = array_filter(explode(',', $this->params()->fromQuery('filters')));
        $geonames = $this->getEntityManager()->getRepository('Application\Model\Geoname')->findById($geonameIds);
        $filters = $this->getEntityManager()->getRepository('Application\Model\Filter')->findById($filtersIds);

        $part = $this->getEntityManager()->getRepository('Application\Model\Part')->findOneById($this->params()->fromQuery('part'));

        $series = [];
        foreach ($geonames as $geoname) {
            $prefix = count($geonames) > 1 ? $geoname->getName() . ' - ' : null;
            $a = $this->getAllSeriesForOneGeoname($geoname, $filters, $part, $prefix);
            $series = array_merge($series, $a);
        }

        $chart = $this->getChart($series, $geonames, $part);
        if (isset($adjusted['overriddenFilters']) && $adjusted['overriddenFilters']) {
            $chart['overriddenFilters'] = $adjusted['overriddenFilters'];
        }
        if (isset($adjusted['originalFilters']) && $adjusted['originalFilters']) {
            $chart['originalFilters'] = $adjusted['originalFilters'];
        }

        return new NumericJsonModel($chart);
    }

    private function getAllSeriesForOneGeoname(Geoname $geoname, $filters, Part $part, $prefix)
    {
        $questionnaires = $this->getEntityManager()->getRepository('Application\Model\Questionnaire')->getAllForComputing($geoname);

        // Compute adjusted series if we asked any
        $adjusted = $this->getAdjustedSeries($geoname, $questionnaires, $part, $prefix);
        $adjustedSeries = $adjusted['series'];

        // First get series of flatten regression lines with ignored values (if any)
        $seriesWithIgnoredElements = $this->computeIgnoredElements($geoname, $filters, $questionnaires, $part, $prefix);

        // Finally we compute "normal" series, and make it "light" if we have alternative series to highlight
        $alternativeSeries = array_merge($seriesWithIgnoredElements, $adjustedSeries);
        $normalSeries = $this->getSeries($geoname, $filters, $questionnaires, $part, $alternativeSeries ? 33 : 100, $alternativeSeries ? 'ShortDot' : null, false, $prefix);

        $newSeries = array_merge($normalSeries, $alternativeSeries);

        // Ensure that series are not added twice to series list
        $series = array();
        foreach ($newSeries as $newSerie) {
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

        return $series;
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
     * @param \Application\Model\Geoname $geoname
     * @param \Application\Model\Filter[] $filters
     * @param array $questionnaires
     * @param \Application\Model\Part $part
     * @return array
     */
    protected function computeIgnoredElements(Geoname $geoname, $filters, array $questionnaires, Part $part, $prefix)
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

            $mySeries = $this->getSeries($geoname, $filters, $questionnairesNotIgnored, $part, 100, null, true, $prefix, ' (ignored elements)', $overriddenFilters);
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
     * Get line and scatter series for the given filters and questionnaires
     * @param \Application\Model\Geoname $geoname
     * @param \Application\Model\Filter[] $filters
     * @param array $questionnaires
     * @param \Application\Model\Part $part
     * @param float $ratio
     * @param string $dashStyle
     * @param bool $isIgnored
     * @param string $prefix for serie name
     * @param string $suffix for serie name
     * @param array $overriddenFilters
     * @internal param array $colors
     * @return array
     */
    private function getSeries(Geoname $geoname, $filters, array $questionnaires, Part $part, $ratio, $dashStyle = null, $isIgnored = false, $prefix = null, $suffix = null, array $overriddenFilters = array(), $isAdjusted = false)
    {
        $this->getCalculator()->setOverriddenFilters($overriddenFilters);

        $lines = $this->getLinedSeries($geoname, $filters, $questionnaires, $part, $ratio, $dashStyle, $isIgnored, $prefix, $suffix, $isAdjusted);
        $scatters = $this->getScatteredSeries($filters, $questionnaires, $part, $ratio, $isIgnored, $prefix, $suffix, $isAdjusted);

        $series = array_merge($lines, $scatters);

        return $series;
    }

    /**
     * Get lines series
     * @param \Application\Model\Geoname $geoname
     * @param \Application\Model\Filter[] $filters
     * @param array $questionnaires
     * @param Part $part
     * @param $ratio
     * @param null $dashStyle
     * @param $isIgnored
     * @param $suffix
     * @param bool $isAdjusted
     * @internal param \Application\Service\Calculator\Calculator $calculator
     * @internal param array $ignoredFilters
     * @return array
     */
    private function getLinedSeries(Geoname $geoname, array $filters, array $questionnaires, Part $part, $ratio, $dashStyle = null, $isIgnored, $prefix, $suffix, $isAdjusted = false)
    {
        $series = array();

        /** @var \Application\Repository\Rule\FilterFilterRepository $filterRepository */
        $filterRepository = $this->getEntityManager()->getRepository('Application\Model\Filter');

        /** @var \Application\Repository\Rule\FilterGeonameUsageRepository $filterGeonameUsageRepo */
        $filterGeonameUsageRepo = $this->getEntityManager()->getRepository('Application\Model\Rule\FilterGeonameUsage');

        $lines = $this->getCalculator()->computeFlattenAllYears($this->startYear, $this->endYear, $filters, $questionnaires, $part);
        foreach ($lines as &$serie) {
            /** @var \Application\Model\Filter $filter */
            $filter = $filterRepository->findOneById($serie['id']);

            /** @var \Application\Repository\Rule\FilterGeonameUsage usages */
            $usages = $filterGeonameUsageRepo->getAllForGeonameAndFilter($geoname, $filter, $part);

            $serie['color'] = $filter->getGenericColor($ratio);
            $serie['type'] = 'line';
            $serie['name'] = $prefix . $serie['name'] . $suffix;

            if ($isIgnored) {
                $serie['isIgnored'] = $isIgnored;
            }
            if ($isAdjusted) {
                $serie['isAdjusted'] = $isAdjusted;
            }
            if ($dashStyle) {
                $serie['dashStyle'] = $dashStyle;
            }

            if (count($usages) > 0) {
                $usageList = implode(',<br/>', array_map(function ($u) {
                            return $u->getRule()->getName();
                        }, $usages));
            } else {
                $usageList = '(none)';
            }
            $serie['usages'] = $usageList;

            foreach ($serie['data'] as &$d) {
                $d = Utility::decimalToRoundedPercent($d);
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
     * @param bool $isAdjusted
     * @return array
     */
    private function getScatteredSeries($filters, array $questionnaires, Part $part, $ratio, $isIgnored, $prefix, $suffix, $isAdjusted = false)
    {
        $series = array();

        // Then add scatter points which are each questionnaire values
        foreach ($filters as $filter) {
            $idFilter = $filter->getId();
            $data = $this->getCalculator()->computeFilterForAllQuestionnaires($filter->getId(), $questionnaires, $part->getId());
            $scatter = array(
                'type' => 'scatter',
                'id' => $filter->getId(),
                'color' => $filter->getGenericColor($ratio),
                'marker' => array('symbol' => $this->symbols[$this->getConstantKey($filter->getName()) % count($this->symbols)]),
                'name' => $prefix . $filter->getName() . $suffix,
                'allowPointSelect' => false,
                'data' => array(), // because we will use our own click handler
            );
            if ($isIgnored) {
                $scatter['isIgnored'] = $isIgnored;
            }
            if ($isAdjusted) {
                $scatter['isAdjusted'] = $isAdjusted;
            }

            foreach ($data['values'] as $questionnaireId => $value) {

                if (!is_null($value)) {
                    $scatterData = array(
                        'name' => $data['surveys'][$questionnaireId],
                        'id' => $idFilter . ':' . $questionnaireId,
                        'questionnaire' => $questionnaireId,
                        'x' => $data['years'][$questionnaireId],
                        'y' => Utility::decimalToRoundedPercent($value),
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

            // create filters objects
            $filtersIds = array_filter(explode(',', $this->params()->fromQuery('filters')));
            $filters = array_map(function($filterId) {
                return $this->getEntityManager()->getRepository('Application\Model\Filter')->findOneById($filterId);
            }, $filtersIds);

            $result = array(
                'name' => $questionnaire->getName(),
                'filters' => array(),
            );
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
            foreach ($filters as $filter) {
                $fields = array_merge(explode(',', $this->params()->fromQuery('fields')), array(
                    'questions',
                    'questions.survey'
                ));

                $tableController = new TableController();
                $tableController->setServiceLocator($this->getServiceLocator());

                $result['filters'][$filter->getId()] = $tableController->computeWithChildren($questionnaire, $filter, array($part), 0, $fields, array(), true);
                $resultWithoutIgnoredFilters['filters'][$filter->getId()] = $tableController->computeWithChildren($questionnaire, $filter, array($part), 0, $fields, $overriddenElements, true);

                foreach ($result['filters'][$filter->getId()] as $i => &$flatFilter) {
                    $flatFilter = $this->addComputedValuesToFilters($flatFilter, $part, $resultWithoutIgnoredFilters['filters'][$filter->getId()][$i]['values']);
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
                $value = $this->getCalculator()->computeFormulaBasic($usage);
                $value = Utility::decimalToRoundedPercent($value);
                $extractedUsage['value'] = $value;
                $extractedUsages[] = $extractedUsage;
            }
        }

        return $extractedUsages;
    }

    /**
     * Return an array of series and overridden filters, if were asked to do it
     * @param \Application\Model\Geoname $geoname
     * @param array $questionnaires
     * @param \Application\Model\Part $part
     * @return array ['series' => series, 'overriddenFilters' => overriddenFilters]
     */
    private function getAdjustedSeries(Geoname $geoname, array $questionnaires, Part $part, $prefix)
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

                $adjustedSeries = $this->getSeries($geoname, [$reference], $questionnaires, $part, 100, null, false, $prefix, ' (adjusted)', $overriddenFilters, true);
                $originalSeries = $this->getSeries($geoname, [$reference], $questionnaires, $part, 33, 'ShortDot', false, $prefix, '', array(), true);
                $ancestorsSeries = $this->getAncestorsSeries($geoname, $reference, $questionnaires, $part, $overriddenFilters, $prefix);

                $series = array_merge($adjustedSeries, $originalSeries, $ancestorsSeries);
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
     * @param Geoname $geoname
     * @param Filter $reference
     * @param array $questionnaires
     * @param Part $part
     * @param array $overriddenFilters
     * @return array
     */
    private function getAncestorsSeries(Geoname $geoname, Filter $reference, array $questionnaires, Part $part, array $overriddenFilters, $prefix)
    {
        $topLevelFilters = $reference->getRootAncestors();
        $topLevelFiltersSeries = array();
        foreach ($topLevelFilters as $filter) {
            $topLevelFiltersSeries = array_merge($topLevelFiltersSeries, $this->getSeries($geoname, [$filter], $questionnaires, $part, 100, null, false, $prefix, ' (adjusted)', $overriddenFilters, true));
            $topLevelFiltersSeries = array_merge($topLevelFiltersSeries, $this->getSeries($geoname, [$filter], $questionnaires, $part, 33, 'ShortDot', false, $prefix, '', array(), true));
        }

        return $topLevelFiltersSeries;
    }

}
