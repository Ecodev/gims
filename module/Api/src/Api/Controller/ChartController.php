<?php

namespace Api\Controller;

use Application\View\Model\NumericJsonModel;
use Application\Model\FilterSet;
use Application\Model\Part;

class ChartController extends \Application\Controller\AbstractAngularActionController
{

    private $colors = array('#2f7ed8', '#0d233a', '#8bbc21', '#910000', '#1aadce', '#492970', '#f28f43', '#77a1e5', '#c42525', '#a6c96a');
    private $lightColors = array('#d4e4f7', '#d3d3d3', '#ddf1b0', '#ffa5a5', '#b6eaf6', '#d4c3e9', '#fad1b1', '#dae5f8', '#f2bbbb', '#dae8c0');
    private $symbols = array('circle', 'diamond', 'square', 'triangle', 'triangle-down');
    private $startYear;
    private $endYear;
    private $excludedQuestionnaires;

    public function indexAction()
    {
        $country = $this->getEntityManager()->getRepository('Application\Model\Country')->findOneById($this->params()->fromQuery('country'));

        /** @var \Application\Model\FilterSet $filterSet */
        $filterSet = $this->getEntityManager()->getRepository('Application\Model\FilterSet')->findOneById($this->params()->fromQuery('filterSet'));
        $part = $this->getEntityManager()->getRepository('Application\Model\Part')->findOneById($this->params()->fromQuery('part'));
        $excludeStr = $this->params()->fromQuery('excludedQuestionnaires');
        $this->excludedQuestionnaires = $excludeStr ? explode(',', $excludeStr) : array();

        $questionnaires = $this->getEntityManager()->getRepository('Application\Model\Questionnaire')->getByGeonameWithSurvey($country ? $country->getGeoname() : -1);

        $this->startYear = 1980;
        $this->endYear = 2011;

        $series = array();
        if ($filterSet) {

            // First get series of flatten regression lines with excluded values (if any)
            $seriesWithExcludedElements = $this->computeExcludedElements($questionnaires, $part);

            // If the filterSet is a copy of an original FilterSet, then we also display the original (with light colors)
            if ($filterSet->getOriginalFilterSet()) {
                $originalFilterSet = $filterSet->getOriginalFilterSet();
                $seriesWithOriginal = $this->getSeries($originalFilterSet, $questionnaires, $part, array(), $this->colors, null, ' (original)');
            } else {
                $seriesWithOriginal = array();
            }

            $excludedFilters = array();
            foreach ($filterSet->getExcludedFilters() as $excludedFilter) {
                $excludedFilters[] = $excludedFilter->getId();
            }

            // Finally we compute "normal" series, and make it "light" if we have alternative series to highlight
            $alternativeSeries = array_merge($seriesWithExcludedElements, $seriesWithOriginal);
            $normalSeries = $this->getSeries($filterSet, $questionnaires, $part, $excludedFilters, $alternativeSeries ? $this->lightColors : $this->colors, $alternativeSeries ? 'ShortDash' : null);

            $series = array_merge($normalSeries, $alternativeSeries);
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
                'text' => 'Estimated proportion of the population for ' . ($filterSet ? $filterSet->getName() : 'Unkown filterSet'),
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
        // init excluded questionnaires array
        $excludedElementsByFilter = array();
        foreach ($this->excludedQuestionnaires as $r) {
            list($filterId, $questionnaireId) = explode(':', $r);
            if (!array_key_exists($filterId, $excludedElementsByFilter))
                $excludedElementsByFilter[$filterId] = array('questionnaires' => array(),'filtres' => array());
            $excludedElementsByFilter[$filterId]['questionnaires'][] = $questionnaireId;
        }

        // init excludes filters array
        $params = $this->params()->fromQuery('excludedFilters');
        if (!$params) {
            return array();
        }
        $params = explode(',', $params);
        foreach ($params as $r) {
            list($highFilterId, $filterId) = explode(':', $r);
            if (!array_key_exists($highFilterId, $excludedElementsByFilter))
                $excludedElementsByFilter[$highFilterId] = array('questionnaires' => array(),'filtres' => array());
            $excludedElementsByFilter[$highFilterId]['filters'][] = $filterId;
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

            $mySeries = $this->getSeries($filterSetSingle, $questionnairesNotExcluded, $part, $excludedElement['filters'], $this->colors, null, ' (ignored filters)');
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
    protected function getSeries(FilterSet $filterSet, array $questionnaires, Part $part, array $excludedFilters, array $colors, $dashStyle = null, $suffix = null)
    {
        $series = array();
        $calculator = new \Application\Service\Calculator\Jmp();
        $calculator->setServiceLocator($this->getServiceLocator());
        $lines = $calculator->computeFlattenAllYears($this->startYear, $this->endYear, $filterSet, $questionnaires, $part, $excludedFilters);
        foreach ($lines as &$serie) {
            $serie['color'] = $colors[$this->getConstantKey($serie['name']) % count($colors)];
            $serie['name'] .= $suffix;
            $serie['type'] = 'line';

            if ($dashStyle) {
                $serie['dashStyle'] = $dashStyle;
            }

            foreach ($serie['data'] as &$d) {
                if (!is_null($d))
                    $d = \Application\Utility::bcround($d * 100, 1);
            }
            $series[] = $serie;
        }

        // Then add scatter points which are each questionnaire values
        foreach ($filterSet->getFilters() as $filter) {
            $idFilter = $filter->getId();
            $data = $calculator->computeFilterForAllQuestionnaires($filter->getId(), $questionnaires, $part->getId());
            $scatter = array(
                'type' => 'scatter',
                'color' => $colors[$this->getConstantKey($filter->getName()) % count($colors)],
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
                        'y' => \Application\Utility::bcround($value * 100, 1),
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
