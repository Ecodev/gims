<?php

namespace Api\Controller;

use Application\View\Model\NumericJsonModel;
use Application\Model\FilterSet;
use Application\Model\Part;
use Application\Utility;

class ChartController extends \Application\Controller\AbstractAngularActionController
{
    private $symbols = array('circle', 'diamond', 'square', 'triangle', 'triangle-down');
    private $startYear;
    private $endYear;
    private $excludedQuestionnaires;
    private $usedFilters = array();

    public function indexAction()
    {
        $country = $this->getEntityManager()->getRepository('Application\Model\Country')->findOneById($this->params()->fromQuery('country'));

        $filterSetsName = '';
        $filterSets = array();
        $filterSetsIds = explode(',', $this->params()->fromQuery('filterSet'));
        foreach ($filterSetsIds as $filterSetId) {
            if (!empty($filterSetId)) {
                /* @var $filterSet \Application\Model\FilterSet */
                $filterSet = $this->getEntityManager()->getRepository('Application\Model\FilterSet')->findOneById($filterSetId);
                $filterSetsName .= $filterSet->getName() . ', ';
                $filterSets[] = $filterSet;
                $hFilters = $filterSet->getFilters()->map(function($el) {
                    return $el->getId();
                });
                $this->usedFilters = array_merge($this->usedFilters, $hFilters->toArray());
            }
        }
        $filterSetsName = trim($filterSetsName, ", ");

        $part = $this->getEntityManager()->getRepository('Application\Model\Part')->findOneById($this->params()->fromQuery('part'));
        $excludeStr = $this->params()->fromQuery('excludedQuestionnaires');
        $this->excludedQuestionnaires = $excludeStr ? explode(',', $excludeStr) : array();

        $questionnaires = $this->getEntityManager()->getRepository('Application\Model\Questionnaire')->getByGeonameWithSurvey($country ? $country->getGeoname() : -1);

        $this->startYear = 1980;
        $this->endYear = 2012;

        $series = array();
        if (count($filterSets) > 0) {

            // First get series of flatten regression lines with excluded values (if any)
            $seriesWithExcludedElements = $this->computeExcludedElements($questionnaires, $part);

            foreach ($filterSets as $filterSet) {

                // If the filterSet is a copy of an original FilterSet, then we also display the original (with light colors)
                if ($filterSet->getOriginalFilterSet()) {
                    $originalFilterSet = $filterSet->getOriginalFilterSet();
                    $seriesWithOriginal = $this->getSeries($originalFilterSet, $questionnaires, $part, array(), 100, null, ' (original)');
                } else {
                    $seriesWithOriginal = array();
                }

                $excludedFilters = array();
                foreach ($filterSet->getExcludedFilters() as $excludedFilter) {
                    $excludedFilters[] = $excludedFilter->getId();
                }

                // Finally we compute "normal" series, and make it "light" if we have alternative series to highlight
                $alternativeSeries = array_merge($seriesWithExcludedElements, $seriesWithOriginal);

                $normalSeries = $this->getSeries($filterSet, $questionnaires, $part, $excludedFilters, $alternativeSeries ? 33 :100, $alternativeSeries ? 'ShortDash' : null);

                // insure that series are not added twice to series list
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

        $chart = array(
            'chart' => array(
                'zoomType' => 'xy',
                'height' => 600,
                'animation' => false,
            ),
            'title' => array(
                'text' => ($country ? $country->getName() : 'Unknown country') . ' - ' . ($part ? $part->getName() : 'Unkown part'),
            ),
            'subtitle' => array(
                'text' => 'Estimated proportion of the population for ' . (!empty($filterSetsName) ? $filterSetsName : 'Unkown filterSet'),
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
                        'headerFormat' => '<span style="font-size: 10px">Estimate for {point.key}</span><br/>',
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
     * Returns all series for excluded questionnaires AND filters at the same time
     * @param array $questionnaires
     * @param \Application\Model\Part $part
     * @return array
     */
    protected function computeExcludedElements(array $questionnaires, Part $part)
    {
        $excludedElementsByFilter = array();

        // init excluded elements array
        foreach ($this->excludedQuestionnaires as $r) {
            list($hFilterId, $questionnaireId) = explode(':', $r);
            if (in_array($hFilterId, $this->usedFilters)) {
                if (!array_key_exists($hFilterId, $excludedElementsByFilter))
                    $excludedElementsByFilter[$hFilterId] = array('questionnaires' => array(), 'filters' => array());
                $excludedElementsByFilter[$hFilterId]['questionnaires'][] = $questionnaireId;
            }
        }

        // init excludes filters array
        $params = $this->params()->fromQuery('excludedFilters');
        if ($params) {
            $params = explode(',', $params);
            foreach ($params as $r) {
                list($hFilterId, $filterId) = explode(':', $r);
                if (in_array($hFilterId, $this->usedFilters)) {
                    if (!array_key_exists($hFilterId, $excludedElementsByFilter))
                        $excludedElementsByFilter[$hFilterId] = array('questionnaires' => array(), 'filters' => array());
                    $excludedElementsByFilter[$hFilterId]['filters'][] = $filterId;
                }
            }
        }

        $series = array();
        foreach ($excludedElementsByFilter as $filterId => $excludedElement) {

            $filter = $this->getEntityManager()->getRepository('Application\Model\Filter')->findOneById($filterId);
            $filterSetSingle = new \Application\Model\FilterSet();
            $filterSetSingle->addFilter($filter);

            $questionnairesNotExcluded = array();
            foreach ($questionnaires as $questionnaire) {
                if (!in_array($questionnaire->getId(), $excludedElement['questionnaires'])) {
                    $questionnairesNotExcluded[] = $questionnaire;
                }
            }

            $mySeries = $this->getSeries($filterSetSingle, $questionnairesNotExcluded, $part, $excludedElement['filters'], 100, null, ' (ignored elements)');
            $series = array_merge($series, $mySeries);
        }

        return $series;
    }

    /**
     * Get line and scatter series for the given filterSet and questionnaires
     * @param \Application\Model\FilterSet $filterSet
     * @param array $questionnaires
     * @param \Application\Model\Part $part
     * @param array $excludedFilters
     * @param array $colors
     * @param string $dashStyle
     * @param string $suffix for serie name
     * @return string
     */
    protected function getSeries(FilterSet $filterSet, array $questionnaires, Part $part, array $excludedFilters, $ratio, $dashStyle = null, $suffix = null)
    {
        $series = array();
        $calculator = new \Application\Service\Calculator\Jmp();
        $calculator->setServiceLocator($this->getServiceLocator());
        $lines = $calculator->computeFlattenAllYears($this->startYear, $this->endYear, $filterSet, $questionnaires, $part, $excludedFilters);
        foreach ($lines as &$serie) {
            $serie['color'] = Utility::getColor($serie['id'], $ratio);
            $serie['name'] .= $suffix;
            $serie['type'] = 'line';

            if ($dashStyle) {
                $serie['dashStyle'] = $dashStyle;
            }

            foreach ($serie['data'] as &$d) {
                $d = \Application\Utility::decimalToRoundedPercent($d);
            }
            $series[] = $serie;
        }

        // Then add scatter points which are each questionnaire values
        foreach ($filterSet->getFilters() as $filter) {
            $idFilter = $filter->getId();
            $data = $calculator->computeFilterForAllQuestionnaires($filter->getId(), $questionnaires, $part->getId());
            $scatter = array(
                'type' => 'scatter',
                'color' => Utility::getColor($filter->getId(), $ratio),
                'marker' => array('symbol' => $this->symbols[$this->getConstantKey($filter->getName()) % count($this->symbols)]),
                'name' => $filter->getName() . $suffix,
                'allowPointSelect' => false, // because we will use our own click handler
                'data' => array(),
            );

            foreach ($data['values'] as $questionnaireId => $value) {

                if (!is_null($value)) {
                    $scatterData = array(
                        'name' => $data['surveys'][$questionnaireId],
                        'id' => $idFilter . ':' . $questionnaireId,
                        'questionnaire' => $questionnaireId,
                        'x' => $data['years'][$questionnaireId],
                        'y' => \Application\Utility::decimalToRoundedPercent($value),
                    );
                    // select the ignored values
                    if (in_array($idFilter . ':' . $questionnaireId, $this->excludedQuestionnaires)) {
                        $scatterData['selected'] = 'true';
                    }
                    $scatter['data'][] = $scatterData;
                }
            }
            $series[] = $scatter;
        }

        return $series;
    }

}
