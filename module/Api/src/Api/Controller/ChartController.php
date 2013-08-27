<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;
use Application\Model\FilterSet;
use Application\Model\Part;

class ChartController extends \Application\Controller\AbstractAngularActionController
{

    private $colors = array('#2f7ed8', '#0d233a', '#8bbc21', '#910000', '#1aadce', '#492970', '#f28f43', '#77a1e5', '#c42525', '#a6c96a');
    private $lightColors = array('#d4e4f7', '#d3d3d3', '#ddf1b0', '#ffa5a5', '#b6eaf6', '#d4c3e9', '#fad1b1', '#dae5f8', '#f2bbbb', '#dae8c0');
    private $symbols = array('circle', 'diamond', 'square', 'triangle', 'triangle-down');
    private $startYear;
    private $endYear;
    private $excludedAnswers;

    public function indexAction()
    {
        $country = $this->getEntityManager()->getRepository('Application\Model\Country')->findOneById($this->params()->fromQuery('country'));

        /** @var \Application\Model\FilterSet $filterSet */
        $filterSet = $this->getEntityManager()->getRepository('Application\Model\FilterSet')->findOneById($this->params()->fromQuery('filterSet'));
        $part = $this->getEntityManager()->getRepository('Application\Model\Part')->findOneById($this->params()->fromQuery('part'));
        $excludeStr = $this->params()->fromQuery('exclude');
        $this->excludedAnswers = is_string($excludeStr) && strlen($excludeStr) ? explode(',', $excludeStr) : array();

        $questionnaires = $this->getEntityManager()->getRepository('Application\Model\Questionnaire')->findBy(array('geoname' => $country ? $country->getGeoname() : -1));

        $calculator = new \Application\Service\Calculator\Jmp();
        $calculator->setServiceLocator($this->getServiceLocator());

        $this->startYear = 1980;
        $this->endYear = 2011;

        // First get series of flatten regression lines with excluded values (if any)
        $series = $this->computeExcluded($questionnaires, $part, $calculator, $filterSet);

        // If we only want to refresh the lines with ignored values, we return a partial highcharts data with just those series (quicker to refresh)
        if ($this->params()->fromQuery('onlyExcluded')) {
            return new JsonModel($series);
        }

        // Then get series of flatten regression lines
        if ($filterSet) {

            // If the filterSet is a copy of an original FilterSet, then we also display the original (with light colors)
            if ($filterSet->getOriginalFilterSet())
            {
                $originalFilterSet = $filterSet->getOriginalFilterSet();
                $series = array_merge($series, $this->getSeries($originalFilterSet, $questionnaires, $part, array(), $this->lightColors, 'ShortDash', ' (original)'));
            }

            $excludedFilters = array();
            foreach ($filterSet->getExcludedFilters() as $excludedFilter) {
                $excludedFilters[] = $excludedFilter->getId();
            }

            $series = array_merge($series, $this->getSeries($filterSet, $questionnaires, $part, $excludedFilters, $this->colors));
        }


        $chart = array(
            'chart' => array(
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

        return new JsonModel($chart);
    }

    /**
     * @param \Application\Model\Filter $filterSet
     * @param int $currentFilterId
     *
     * @return int
     */
    protected function getPosition($filterSet, $currentFilterId)
    {
        $key = 0;
        if (is_object($filterSet)) {
            foreach ($filterSet->getFilters() as $key => $filter) {
                if ($filter->getId() == $currentFilterId) {
                    break;
                }
            }
        }
        return $key;
    }

    /**
     * @param array $allQuestionnaires
     * @param \Application\Model\Part $part
     * @param \Application\Service\Calculator\Jmp $calculator
     * @param \Application\Model\FilterSet $filterSet
     *
     * @return array
     */
    protected function computeExcluded($allQuestionnaires, $part, $calculator, $filterSet)
    {

        $idFilter = 0;
        $filtersExcluded = array();
        foreach ($this->excludedAnswers as $r) {
            list($idFilter, $surveyCode) = explode(':', $r);
            if (!array_key_exists($idFilter, $filtersExcluded))
                $filtersExcluded[$idFilter] = array();
            $filtersExcluded[$idFilter][] = $surveyCode;
        }

        $key = $this->getPosition($filterSet, $idFilter);

        // If there are excluded answers, compute an additional regression line for each filter they concern
        $series = array();
        foreach ($filtersExcluded as $idFilter => $excludedSurveys) {
            $filter = $this->getEntityManager()->getRepository('Application\Model\Filter')->findOneById($idFilter);
            $filterSetSingle = new \Application\Model\FilterSet();
            $filterSetSingle->addFilter($filter);

            $questionnairesNotExcluded = array();
            foreach ($allQuestionnaires as $questionnaire) {
                if (!in_array($questionnaire->getSurvey()->getCode(), $excludedSurveys)) {
                    $questionnairesNotExcluded[] = $questionnaire;
                }
            }

            // @todo improve this. Code was added just before launch
            $caseQuestionnaireExcluded = $this->params()->fromQuery('caseQuestionnaireExcluded');
            if ($caseQuestionnaireExcluded) {
                $_questionnaires = $questionnairesNotExcluded;
            } else {
                $_questionnaires = $allQuestionnaires;
            }

            $excludedFilters = explode(',', $this->params()->fromQuery('excludedFilters'));

            // @todo for sylvain:  variable $excludedFilters was added by Fabien for excluded filters - as its name indicated.
            $serieWithExcluded = $calculator->computeFlatten($this->startYear, $this->endYear, $filterSetSingle, $_questionnaires, $part, $excludedFilters);

            foreach ($serieWithExcluded as &$serie) {
                $serie['type'] = 'line';
                $serie['color'] = $this->colors[$key];
                $serie['name'] .= ' (ignored answers)';
                $serie['idFilter'] = $idFilter;
                $serie['dashStyle'] = 'ShortDash';
                foreach ($serie['data'] as &$d) {
                    if (!is_null($d))
                        $d = round($d * 100, 1);
                }
                $series[] = $serie;
            }
        }

        // for now just add this condition
        if (!empty($filtersExcluded)) {

            $questionnaireId = explode(',', $this->params()->fromQuery('questionnaire'));
            $filterSetId = explode(',', $this->params()->fromQuery('filterSet'));

            $questionnaires[] = $this->getEntityManager()->getRepository('Application\Model\Questionnaire')->findOneById($questionnaireId);
            $filterSet = $this->getEntityManager()->getRepository('Application\Model\FilterSet')->findOneById($filterSetId);

            // Then add scatter points which are each questionnaire values
            foreach ($filterSet->getFilters() as $filter) {
                $idFilter = $filter->getId();
                $data = $calculator->computeFilterForAllQuestionnaires($filter, $questionnaires, $part);
                $scatter = array(
                    'type' => 'scatter',
                    'name' => $filter->getName() . ' (ignored answers)',
                    'allowPointSelect' => false, // because we will use our own click handler
                    'color' => $this->colors[$key],
                    'marker' => array('symbol' => $this->symbols[$key]),
                    'data' => array(),
                );
                $i = 0;
                foreach ($data['values%'] as $surveyCode => $value) {

                    if (!is_null($value)) {
                        $scatterData = array(
                            'name' => $surveyCode,
                            'id' => $idFilter . ':' . $surveyCode,
                            'questionnaire' => $data['questionnaire'][$surveyCode],
                            'x' => $data['years'][$i],
                            'y' => round(($value) * 100, 1),
                        );
                        // select the ignored values
                        if (in_array($idFilter . ':' . $surveyCode, $this->excludedAnswers)) {
                            $scatterData['selected'] = 'true';
                        }
                        $scatter['data'][] = $scatterData;
                    }
                    $i++;
                }
                if (isset($scatter['data'][0]['selected'])) {
                    $series[] = $scatter;
                }
            }
        }
        // Add scatter point
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
        $lines = $calculator->computeFlatten($this->startYear, $this->endYear, $filterSet, $questionnaires, $part, $excludedFilters);
        foreach ($lines as $key => &$serie) {
            $serie['name'] .= $suffix;
            $serie['color'] = $colors[$key % count($colors)];
            $serie['type'] = 'line';

            if ($dashStyle) {
                $serie['dashStyle'] = $dashStyle;
            }

            foreach ($serie['data'] as &$d) {
                if (!is_null($d))
                    $d = round($d * 100, 1);
            }
            $series[] = $serie;
        }

        // Then add scatter points which are each questionnaire values
        foreach ($filterSet->getFilters() as $key => $filter) {
            $idFilter = $filter->getId();
            $data = $calculator->computeFilterForAllQuestionnaires($filter, $questionnaires, $part);
            $scatter = array(
                'type' => 'scatter',
                'color' => $colors[$key % count($colors)],
                'marker' => array('symbol' => $this->symbols[$key % count($this->symbols)]),
                'name' => $filter->getName() . $suffix,
                'allowPointSelect' => false, // because we will use our own click handler
                'data' => array(),
            );
            $i = 0;
            foreach ($data['values%'] as $surveyCode => $value) {

                if (!is_null($value)) {
                    $scatterData = array(
                        'name' => $surveyCode,
                        'id' => $idFilter . ':' . $surveyCode,
                        'questionnaire' => $data['questionnaire'][$surveyCode],
                        'x' => $data['years'][$i],
                        'y' => round($value * 100, 1),
                    );
                    // select the ignored values
                    if (in_array($idFilter . ':' . $surveyCode, $this->excludedAnswers)) {
                        $scatterData['selected'] = 'true';
                    }
                    $scatter['data'][] = $scatterData;
                }
                $i++;
            }
            $series[] = $scatter;
        }

        return $series;
    }

}

