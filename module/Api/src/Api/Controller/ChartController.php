<?php

namespace Api\Controller;

use Application\View\Model\NumericJsonModel;
use Application\Model\Part;
use Application\Model\Geoname;
use Application\Model\Questionnaire;
use Application\Utility;
use Application\Service\Hydrator;

class ChartController extends \Application\Controller\AbstractAngularActionController
{

    private $alternativeSeriesExists = false;
    private $symbols = [
        'circle',
        'diamond',
        'square',
        'triangle',
        'triangle-down'
    ];

    /**
     * Dash styles that are acceptable to be displayed all at
     * once on the same graph and still be somewhat readable.
     * @var array
     */
    private $dashStyles = [
        'Solid',
        'Dash',
        'LongDashDot',
        'ShortDashDot',
        'LongDashDotDot',
        'ShortDashDotDot',
    ];
    private $startYear = 1980;
    private $endYear = 2012;

    /**
     * @var \Application\Service\Calculator\Calculator
     */
    private $calculator;

    /**
     * Get the calculator shared instance
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
     * @var \Application\Service\Calculator\Aggregator
     */
    private $aggregator;

    /**
     * Get the aggregator shared instance
     * @return \Application\Service\Calculator\Aggregator
     */
    private function getAggregator()
    {
        if (!$this->aggregator) {
            $this->aggregator = new \Application\Service\Calculator\Aggregator();
            $this->aggregator->setCalculator($this->getCalculator());
        }

        return $this->aggregator;
    }

    /**
     * Return the entire structure to draw the chart
     * @param array $series
     * @param array $geonames
     * @param \Application\Model\Part $part
     * @return array
     */
    private function getChart(array $series, array $geonames, Part $part = null)
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

        return new NumericJsonModel($chart);
    }

    /**
     * Returns all series for one geoname
     * @param \Application\Model\Geoname $geoname
     * @param array $filters
     * @param \Application\Model\Part $part
     * @param string $prefix
     * @return array
     */
    private function getAllSeriesForOneGeoname(Geoname $geoname, array $filters, Part $part, $prefix)
    {
        // Compute adjusted series if we asked any
        $adjustedSeries = $this->getAdjustedSeries($geoname, $filters, $part, $prefix);

        // Compute series with ignored values, if any
        $seriesWithIgnoredElements = $this->getIgnoredSeries($geoname, $filters, $part, $prefix);

        // Finally we compute "normal" series, and make it "light" if we have alternative series to highlight
        $alternativeSeries = array_merge($seriesWithIgnoredElements, $adjustedSeries);
        $this->alternativeSeriesExists = count($alternativeSeries) > 0;
        $normalSeries = $this->getSeries($geoname, $filters, $part, $prefix);

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
     * Always returns the same value from $data for the same name
     * @staticvar array $keys
     * @param string $name
     * @return mixed one of $data values
     */
    private function getConstantValue($name, array $data)
    {
        static $usedKeyCache = array();

        $cacheKey = Utility::getCacheKey($data);
        if (!isset($usedKeyCache[$cacheKey])) {
            $usedKeyCache[$cacheKey] = [];
        }

        if (!array_key_exists($name, $usedKeyCache[$cacheKey])) {
            $usedKeyCache[$cacheKey][$name] = count($usedKeyCache[$cacheKey]);
        }

        $key = $usedKeyCache[$cacheKey][$name];
        $moduloKey = $key % count($data);

        return $data[$moduloKey];
    }

    /**
     * Returns an optional suffix for the serie name
     * @param boolean $isIgnored
     * @param boolean $isAdjusted
     * @return string|null
     */
    private function getSuffix($isIgnored, $isAdjusted)
    {
        if ($isIgnored) {
            return ' (ignored elements)';
        } elseif ($isAdjusted) {
            return ' (adjusted)';
        } else {
            return null;
        }
    }

    /**
     * Returns the dash style of a line serie
     * @param string $name
     * @param boolean $isIgnored
     * @param boolean $isAdjusted
     * @return string
     */
    private function getDashStyle($name, $isIgnored, $isAdjusted)
    {
        // Normal series are always ShortDot if there is any alternative series
        if ($this->alternativeSeriesExists && !$isIgnored && !$isAdjusted) {
            return 'ShortDot';
        } else {
            return $this->getConstantValue($name, $this->dashStyles);
        }
    }

    /**
     * Returns the saturation to be used for a serie color
     * @param boolean $isIgnored
     * @param boolean $isAdjusted
     * @return int
     */
    private function getColorSaturation($isIgnored, $isAdjusted)
    {
        // Normal series are always semi-transparent if there is any alternative series
        if ($this->alternativeSeriesExists && !$isIgnored && !$isAdjusted) {
            return 15;
        } else {
            return 100;
        }
    }

    /**
     * Returns all series for ignored questionnaires AND filters at the same time
     * @param \Application\Model\Geoname $geoname
     * @param \Application\Model\Filter[] $filters
     * @param \Application\Model\Part $part
     * @return array
     */
    private function getIgnoredSeries(Geoname $geoname, array $filters, Part $part, $prefix)
    {
        $overriddenFilters = $this->getIgnoredElements($part);

        if ($overriddenFilters) {
            return $this->getSeries($geoname, $filters, $part, $prefix, $overriddenFilters, true);
        } else {
            return [];
        }
    }

    /**
     * Retrieve ignored elements and return un associative array where
     * key is questionnaire Id and value is a list of ignored filters
     * @param \Application\Model\Part $part
     * @return array
     */
    private function getIgnoredElements(Part $part)
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
     * @param \Application\Model\Questionnaire[] $questionnaires
     * @param \Application\Model\Part $part
     * @param string $prefix for serie name
     * @param array $overriddenFilters
     * @param boolean $isIgnored
     * @param boolean $isAdjusted
     * @internal param array $colors
     * @return array
     */
    private function getSeries(Geoname $geoname, array $filters, Part $part, $prefix = null, array $overriddenFilters = array(), $isIgnored = false, $isAdjusted = false)
    {
        $this->getCalculator()->setOverriddenFilters($overriddenFilters);

        $lines = $this->getLinedSeries($geoname, $filters, $part, $prefix, $isIgnored, $isAdjusted);
        $scatters = $this->getScatteredSeries($geoname, $filters, $part, $prefix, $isIgnored, $isAdjusted);

        $series = array_merge($lines, $scatters);

        // Mark ignored or adjusted series
        foreach ($series as &$serie) {
            if ($isIgnored) {
                $serie['isIgnored'] = $isIgnored;
            }

            if ($isAdjusted) {
                $serie['isAdjusted'] = $isAdjusted;
            }
        }

        return $series;
    }

    /**
     * Get lines series
     * @param \Application\Model\Geoname $geoname
     * @param \Application\Model\Filter[] $filters
     * @param Part $part
     * @param $isIgnored
     * @param bool $isAdjusted
     * @internal param \Application\Service\Calculator\Calculator $calculator
     * @internal param array $ignoredFilters
     * @return array
     */
    private function getLinedSeries(Geoname $geoname, array $filters, Part $part, $prefix = null, $isIgnored = false, $isAdjusted = false)
    {
        $series = array();

        /** @var \Application\Repository\Rule\FilterFilterRepository $filterRepository */
        $filterRepository = $this->getEntityManager()->getRepository('Application\Model\Filter');

        /** @var \Application\Repository\Rule\FilterGeonameUsageRepository $filterGeonameUsageRepo */
        $filterGeonameUsageRepo = $this->getEntityManager()->getRepository('Application\Model\Rule\FilterGeonameUsage');

        $lines = $this->getAggregator()->computeFlattenAllYears($this->startYear, $this->endYear, $filters, $geoname, $part);
        foreach ($lines as &$serie) {
            /** @var \Application\Model\Filter $filter */
            $filter = $filterRepository->findOneById($serie['id']);

            /** @var \Application\Repository\Rule\FilterGeonameUsage usages */
            $usages = $filterGeonameUsageRepo->getAllForGeonameAndFilter($geoname, $filter, $part);

            $baseName = $prefix . $serie['name'];
            $serie['color'] = $filter->getGenericColor($this->getColorSaturation($isIgnored, $isAdjusted));
            $serie['type'] = 'line';
            $serie['name'] = $baseName . $this->getSuffix($isIgnored, $isAdjusted);
            $serie['dashStyle'] = $this->getDashStyle($prefix, $isIgnored, $isAdjusted);
            $serie['marker'] = array('symbol' => $this->getConstantValue($baseName, $this->symbols));

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
     * @param \Application\Model\Geoname $geoname
     * @param \Application\Model\Filter[] $filters
     * @param Part $part
     * @param boolean $isIgnored
     * @param boolean $isAdjusted
     * @return array
     */
    private function getScatteredSeries(Geoname $geoname, array $filters, Part $part, $prefix, $isIgnored = false, $isAdjusted = false)
    {
        $series = array();

        // Then add scatter points which are each questionnaire values
        foreach ($filters as $filter) {
            $idFilter = $filter->getId();
            $data = $this->getAggregator()->computeFilterForAllQuestionnaires($filter->getId(), $geoname, $part->getId());
            $baseName = $prefix . $filter->getName();
            $scatter = array(
                'type' => 'scatter',
                'id' => $filter->getId(),
                'color' => $filter->getGenericColor($this->getColorSaturation($isIgnored, $isAdjusted)),
                'marker' => array('symbol' => $this->getConstantValue($baseName, $this->symbols)),
                'name' => $baseName . $this->getSuffix($isIgnored, $isAdjusted),
                'allowPointSelect' => false,
                'country' => $geoname->getName(),
                'data' => array(), // because we will use our own click handler
            );

            foreach ($data['values'] as $questionnaireId => $value) {

                if (!is_null($value)) {
                    $scatterData = array(
                        'name' => $data['surveys'][$questionnaireId],
                        'id' => $idFilter . ':' . $questionnaireId,
                        'questionnaire' => $questionnaireId,
                        'x' => $data['years'][$questionnaireId],
                        'y' => Utility::decimalToRoundedPercent($value),
                    );

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

            if ($this->params()->fromQuery('getQuestionnaireUsages') === 'true') {
                $usages = $this->extractUsages($questionnaire->getQuestionnaireUsages(), $part);
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
                $fields = explode(',', $this->params()->fromQuery('fields'));

                $tableController = new TableController();
                $tableController->setServiceLocator($this->getServiceLocator());

                $flatFilters = $tableController->computeWithChildren($questionnaire, $filter, array($part), 0, $fields, array(), true);
                $flatFiltersWithoutIgnoredFilters = $tableController->computeWithChildren($questionnaire, $filter, array($part), 0, [], $overriddenElements, true);

                $flatFilters = $this->addQuestionsToFilters($flatFilters, $questionnaire);
                foreach ($flatFilters as $filterId => &$flatFilter) {
                    $flatFilter = $this->addComputedValuesToFilters($flatFilter, $part, $flatFiltersWithoutIgnoredFilters[$filterId]['values']);
                    $flatFilter = $this->addUsagesToFilters($flatFilter, $part, $questionnaire);
                }

                $result['filters'][$filter->getId()] = $flatFilters;
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
     * @param array $flatFilter
     * @param Part $part
     * @param Questionnaire $questionnaire
     * @return array $flatFilter with modifications
     */
    private function addUsagesToFilters(array $flatFilter, Part $part, Questionnaire $questionnaire)
    {
        $fqus = $this->getEntityManager()->getRepository('Application\Model\Rule\FilterQuestionnaireUsage')->getAll($questionnaire->getId(), $flatFilter['filter']['id'], $part->getId());
        $flatFilter['usages'] = $this->extractUsages($fqus, $part);

        return $flatFilter;
    }

    /**
     * Add orignial denomations (questions labels) to given filter ($flatFilter)
     * @param array $flatFilters
     * @param Questionnaire $questionnaire
     * @return array $flatFilter with modifications
     */
    private function addQuestionsToFilters(array $flatFilters, Questionnaire $questionnaire)
    {
        $filterIds = array_map(function($flatFilter) {
            return $flatFilter['filter']['id'];
        }, $flatFilters);

        $alternateNames = $this->getEntityManager()->getRepository('Application\Model\Question\AbstractQuestion')->getByFiltersAndQuestionnaire($filterIds, $questionnaire);

        foreach ($alternateNames as $alternateName) {
            $name = $alternateName['alternateNames'][$questionnaire->getId()];
            foreach ($flatFilters as &$flatFilter) {
                if ($flatFilter['filter']['id'] == $alternateName['filterId']) {
                    $flatFilter['filter']['originalDenomination'] = $name;
                }
            }
        }

        return $flatFilters;
    }

    /**
     *
     * @param \Application\Model\Rule\AbstractQuestionnaireUsage[] $usages
     * @param Part $part
     * @return array
     */
    private function extractUsages($usages, Part $part)
    {
        $hydrator = new Hydrator();
        $extractedUsages = [];
        foreach ($usages as $usage) {
            if ($usage->getPart() === $part) {
                $extractedUsage = $hydrator->extract($usage, array(
                    'part',
                    'rule',
                    'rule.name',
                ));

                if ($usage instanceof \Application\Model\Rule\QuestionnaireUsage) {
                    $value = $this->getCalculator()->computeFormulaBasic($usage);
                    $roundedValue = Utility::decimalToRoundedPercent($value);
                    $extractedUsage['value'] = $roundedValue;
                }

                $extractedUsages[] = $extractedUsage;
            }
        }

        return $extractedUsages;
    }

    /**
     * Return an array of series (with their overridden filters), if were asked to do it
     * @param \Application\Model\Geoname $geoname
     * @param \Application\Model\Filter[] $filters
     * @param \Application\Model\Part $part
     * @return array
     */
    private function getAdjustedSeries(Geoname $geoname, array $filters, Part $part, $prefix)
    {
        $referenceId = $this->params()->fromQuery('reference');
        $overridableId = $this->params()->fromQuery('overridable');
        @list($targetId, $includeIgnoredElements) = explode(':', $this->params()->fromQuery('target'));

        // bail out early to avoid useless SQL query
        if (!$referenceId || !$overridableId || !$targetId) {
            return [];
        }

        $reference = $this->getEntityManager()->getRepository('Application\Model\Filter')->findOneById($referenceId);
        $overridable = $this->getEntityManager()->getRepository('Application\Model\Filter')->findOneById($overridableId);
        $target = $this->getEntityManager()->getRepository('Application\Model\Filter')->findOneById($targetId);

        if (!$reference || !$overridable || !$target) {
            return [];
        }

        $adjustator = new \Application\Service\Calculator\Adjustator();
        $adjustator->setAggregator($this->getAggregator());

        $originalFilters = $adjustator->getOriginalOverrideValues($target, $reference, $overridable, $geoname, $part);

        if ($includeIgnoredElements) {
            $ignoredElements = $this->getIgnoredElements($part);
            $this->getCalculator()->setOverriddenFilters($ignoredElements);
        }

        $overriddenFilters = $adjustator->findOverriddenFilters($target, $reference, $overridable, $geoname, $part);
        $this->getCalculator()->setOverriddenFilters($overriddenFilters);

        $adjustedSeries = $this->getSeries($geoname, $filters, $part, $prefix, $overriddenFilters, false, true);

        // Inject extra data about adjustement
        $adjustedSeries[0]['overriddenFilters'] = $overriddenFilters;
        $adjustedSeries[0]['originalFilters'] = $originalFilters;

        return $adjustedSeries;
    }

}
