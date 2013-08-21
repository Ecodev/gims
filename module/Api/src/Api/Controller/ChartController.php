<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class ChartController extends \Application\Controller\AbstractAngularActionController
{

    private $colors
        = array('#2f7ed8', '#0d233a', '#8bbc21', '#910000', '#1aadce', '#492970', '#f28f43', '#77a1e5',
                '#c42525', '#a6c96a'); // not the best way but since the whole class is a mess it does not hurt. ;)

    private $lightColors
        = array('#d4e4f7', '#d3d3d3', '#ddf1b0', '#ffa5a5', '#b6eaf6', '#d4c3e9', '#fad1b1', '#dae5f8',
                '#f2bbbb', '#dae8c0'); // not the best way but since the whole class is a mess it does not hurt. ;)

    private $symbols = array('circle', 'diamond', 'square', 'triangle', 'triangle-down');

    public function indexAction()
    {
        $country = $this->getEntityManager()->getRepository('Application\Model\Country')->findOneById($this->params()->fromQuery('country'));

        /** @var \Application\Model\FilterSet $filterSet */
        $filterSet = $this->getEntityManager()->getRepository('Application\Model\FilterSet')->findOneById($this->params()->fromQuery('filterSet'));
        $part = $this->getEntityManager()->getRepository('Application\Model\Part')->findOneById($this->params()->fromQuery('part'));
        $excludeStr = $this->params()->fromQuery('exclude');
        $excludedAnswers = is_string($excludeStr)&&strlen($excludeStr) ? split(',', $excludeStr) : array();

        $questionnaires = $this->getEntityManager()->getRepository('Application\Model\Questionnaire')->findBy(array('geoname' => $country ? $country->getGeoname() : -1));

        $calculator = new \Application\Service\Calculator\Jmp();
        $calculator->setServiceLocator($this->getServiceLocator());

        $startYear = 1980;
        $endYear = 2011;

        // First get series of flatten regression lines with excluded values (if any)
        $series = $this->computeExcluded($excludedAnswers, $questionnaires, $startYear, $endYear, $part, $calculator,
            $filterSet);

        // If we only want to refresh the lines with ignored values, we return a partial highcharts data with just those series (quicker to refresh)
        if ($this->params()->fromQuery('onlyExcluded'))
        {
            return new JsonModel($series);
        }

        // Then get series of flatten regression lines
        if ($filterSet) {

            $excludedFilters = array();
            /** @var \Application\Model\Filter $excludedFilter */
            foreach ($filterSet->getExcludedFilters() as $excludedFilter) {
                $excludedFilters[] = $excludedFilter->getId();
            }

            $shortDash = '';
            /////////////////////////////////////////
            // @todo clean me up! This trick was implemented a day before the launch. It must be cleaned up.
            if (false && $filterSet->getId() > 6) {

                // exchange value
                $color = $this->colors;
                $this->colors = $this->lightColors;
                $this->lightColors = $color;

                // Check the filter source
                $filterId = $filterSet->getFilters()->first()->getId();

                // true means this is filter set 1
                if (in_array($filterId, array(2, 8, 51, 54, 57, 65, 68, 73))) {
                    $filterSetAssumed = 1;
                } elseif (in_array($filterId, array(121, 127, 145, 189, 192, 195, 196, 199))) {
                    $filterSetAssumed = 3;
                } elseif (in_array($filterId, array(74, 75))) {
                    $filterSetAssumed = 5;
                } else {
                    $filterSetAssumed = 6;
                }
                $filterSetOfficial = $this->getEntityManager()->getRepository('Application\Model\FilterSet')->findOneById($filterSetAssumed);

                $lines = $calculator->computeFlatten(
                    $startYear, $endYear, $filterSetOfficial, $questionnaires, $part
                );

                foreach ($lines as $key => &$serie) {
                    $serie['name'] = $serie['name'] . ' (official)';
                    $serie['color'] = $this->lightColors[$key];
                    $serie['type'] = 'line';
                    foreach ($serie['data'] as &$d) {
                        if (!is_null($d))
                            $d = round($d * 100, 1);
                    }
                    $series[] = $serie;
                }

                // Then add scatter points which are each questionnaire values
                foreach ($filterSetOfficial->getFilters() as $key => $filter) {
                    $idFilter = $filter->getId();
                    $data = $calculator->computeFilterForAllQuestionnaires($filter, $questionnaires, $part);
                    $scatter = array(
                        'type'             => 'scatter',
                        'color'            => $this->lightColors[$key],
                        'marker'           => array('symbol' => $this->symbols[$key]),
                        'name'             => $filter->getName(),
                        'allowPointSelect' => false, // because we will use our own click handler
                        'data'             => array(),
                    );
                    $i = 0;
                    foreach ($data['values%'] as $surveyCode => $value) {

                        if (!is_null($value)) {
                            $scatterData = array(
                                'name'          => $surveyCode,
                                'id'            => $idFilter . ':' . $surveyCode,
                                'questionnaire' => $data['questionnaire'][$surveyCode],
                                'x'             => $data['years'][$i],
                                'y'             => round($value * 100, 1),
                            );
                            // select the ignored values
                            if (in_array($idFilter . ':' . $surveyCode, $excludedAnswers)) {
                                $scatterData['selected'] = 'true';
                            }
                            $scatter['data'][] = $scatterData;
                        }
                        $i++;
                    }
                    $series[] = $scatter;
                }
                $shortDash = 'ShortDash';
            }

            /////////////////////////////////////////
            $calculator = new \Application\Service\Calculator\Jmp();
            $calculator->setServiceLocator($this->getServiceLocator());
            $lines = $calculator->computeFlatten($startYear, $endYear, $filterSet, $questionnaires, $part, $excludedFilters);
            foreach ($lines as $key => &$serie) {
                $serie['color'] = $this->colors[$key % count($this->colors)];
                $serie['type'] = 'line';
                $serie['dashStyle'] = $shortDash;
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
                    'color' => $this->colors[$key % count($this->colors)],
                    'marker' => array('symbol' => $this->symbols[$key % count($this->symbols)]),
                    'name' => $filter->getName(),
                    'allowPointSelect' => false, // because we will use our own click handler
                    'data' => array(),
                );
                $i = 0;
                foreach ($data['values%'] as $surveyCode => $value) {

                    if (!is_null($value)) {
                        $scatterData = array(
                            'name' => $surveyCode,
                            'id' => $idFilter.':'.$surveyCode,
                            'questionnaire' => $data['questionnaire'][$surveyCode],
                            'x' => $data['years'][$i],
                            'y' => round($value * 100, 1),
                        );
                        // select the ignored values
                        if (in_array($idFilter.':'.$surveyCode, $excludedAnswers))
                        {
                            $scatterData['selected'] = 'true';
                        }
                        $scatter['data'][] = $scatterData;
                    }
                    $i++;
                }
                $series[] = $scatter;
            }
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
                    'pointStart' => $startYear,
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
                        "pointFormat"  => '<b>{point.name}</b><br/>{point.y}% ({point.x})'
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
    protected function getPosition($filterSet, $currentFilterId) {
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
     * @param array $excludedAnswers
     * @param array $allQuestionnaires
     * @param int $startYear
     * @param int $endYear
     * @param \Application\Model\Part $part
     * @param \Application\Service\Calculator\Jmp $calculator
     * @param \Application\Model\FilterSet $filterSet
     *
     * @return array
     */
    protected function computeExcluded($excludedAnswers, $allQuestionnaires, $startYear, $endYear, $part, $calculator, $filterSet)
    {

        $idFilter = 0;
        $filtersExcluded = array();
        foreach($excludedAnswers as $r) {
            list($idFilter, $surveyCode) = split(':', $r);
            if (!array_key_exists($idFilter, $filtersExcluded))
                $filtersExcluded[$idFilter] = array();
            $filtersExcluded[$idFilter][] = $surveyCode;
        }

        $key = $this->getPosition($filterSet, $idFilter);

        // If there are excluded answers, compute an additional regression line for each filter they concern
        $series = array();
        foreach($filtersExcluded as $idFilter => $excludedSurveys)
        {
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
            $serieWithExcluded = $calculator->computeFlatten($startYear, $endYear, $filterSetSingle,
                $_questionnaires, $part, $excludedFilters);

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
        if (! empty($filtersExcluded)) {

            $questionnaireId = explode(',', $this->params()->fromQuery('questionnaire'));
            $filterSetId = explode(',', $this->params()->fromQuery('filterSet'));

            $questionnaires[] = $this->getEntityManager()->getRepository('Application\Model\Questionnaire')->findOneById($questionnaireId);
            $filterSet = $this->getEntityManager()->getRepository('Application\Model\FilterSet')->findOneById($filterSetId);

            // Then add scatter points which are each questionnaire values
            foreach ($filterSet->getFilters() as $filter) {
                $idFilter = $filter->getId();
                $data = $calculator->computeFilterForAllQuestionnaires($filter, $questionnaires, $part);
                $scatter = array(
                    'type'             => 'scatter',
                    'name'             => $filter->getName()  . ' (ignored answers)',
                    'allowPointSelect' => false, // because we will use our own click handler
                    'color'            => $this->colors[$key],
                    'marker'           => array('symbol' => $this->symbols[$key]),
                    'data'             => array(),

                );
                $i = 0;
                foreach ($data['values%'] as $surveyCode => $value) {

                    if (!is_null($value)) {
                        $scatterData = array(
                            'name'          => $surveyCode,
                            'id'            => $idFilter . ':' . $surveyCode,
                            'questionnaire' => $data['questionnaire'][$surveyCode],
                            'x'             => $data['years'][$i],
                            'y'             => round(($value) * 100, 1),
                        );
                        // select the ignored values
                        if (in_array($idFilter . ':' . $surveyCode, $excludedAnswers)) {
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

}
