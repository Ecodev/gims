<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class ChartController extends \Application\Controller\AbstractAngularActionController
{

    public function indexAction()
    {
        $country = $this->getEntityManager()->getRepository('Application\Model\Country')->findOneById($this->params()->fromQuery('country'));
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
        $series = $this->computeExcluded($excludedAnswers, $questionnaires, $startYear, $endYear, $part, $calculator);

        // If we only want to refresh the lines with ignored values, we return a partial highcharts data with just those series (quicker to refresh)
        if ($this->params()->fromQuery('onlyExcluded'))
        {
            return new JsonModel($series);
        }

        // Then get series of flatten regression lines
        $filterCount = 0;
        if ($filterSet) {
            $filterCount = $filterSet->getFilters()->count();
            $lines = $calculator->computeFlatten($startYear, $endYear, $filterSet, $questionnaires, $part);
            foreach ($lines as &$serie) {
                $serie['type'] = 'line';
                foreach ($serie['data'] as &$d) {
                    if (!is_null($d))
                        $d = round($d * 100, 1);
                }
                $series[] = $serie;
            }

            // Then add scatter points which are each questionnaire values
            foreach ($filterSet->getFilters() as $filter) {
                $idFilter = $filter->getId();
                $data = $calculator->computeFilterForAllQuestionnaires($filter, $questionnaires, $part);
                $scatter = array(
                    'type' => 'scatter',
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
            // Here we use default highchart colors and symbols, but truncated at the same number of series,
            // so it will get repeated for lines and scatter points
            'colors' => array_slice(array('#2f7ed8', '#0d233a', '#8bbc21', '#910000', '#1aadce', '#492970', '#f28f43', '#77a1e5', '#c42525', '#a6c96a'), 0, $filterCount),
            'symbols' => array_slice(array('circle', 'diamond', 'square', 'triangle', 'triangle-down'), 0, $filterCount),
            'title' => array(
                'text' => ($country ? $country->getName() : 'Unknown country') . ' - ' . ($part ? $part->getName() : 'Total'),
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

    protected function computeExcluded($excludedAnswers, $allQuestionnaires, $startYear, $endYear, $part, $calculator)
    {
        $filtersExcluded = array();
        foreach($excludedAnswers as $r) {
            list($idFilter, $surveyCode) = split(':', $r);
            if (!array_key_exists($idFilter, $filtersExcluded))
                $filtersExcluded[$idFilter] = array();
            $filtersExcluded[$idFilter][] = $surveyCode;
        }
        // If there are excluded answers, compute an additional regression line for each filter they concern
        $series = array();
        foreach($filtersExcluded as $idFilter => $excludedSurveys)
        {
            $filter = $this->getEntityManager()->getRepository('Application\Model\Filter')->findOneById($idFilter);
            $filterSetSingle = new \Application\Model\FilterSet();
            $filterSetSingle->addFilter($filter);
            $questionnairesNotExcluded = array();
            foreach($allQuestionnaires as $questionnaire)
            {
                if (!in_array($questionnaire->getSurvey()->getCode(), $excludedSurveys)) {
                    $questionnairesNotExcluded[] = $questionnaire;
                }
            }
            $serieWithExcluded = $calculator->computeFlatten($startYear, $endYear, $filterSetSingle, $questionnairesNotExcluded, $part);
            foreach ($serieWithExcluded as &$serie) {
                $serie['type'] = 'line';
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

        // @todo sylvain, can you continue this? $excludedFilters contains the excluded filters
        $excludedFilters = explode(',', $this->params()->fromQuery('excludedFilters'));
        if (! empty($series)) {

            $series[] = array(
                "type"             => "scatter",
                "name"             => "Piped onto premises (ignored answers)",
                "allowPointSelect" => false,
                "data"             => array(
                    array(
                        "name"          => "SAGE08",
                        "id"            => "75:SAGE08",
                        "questionnaire" => 168,
                        "x"             => 2008,
                        "y"             => 85,
                        "selected"      => "true"
                    )
                )
            );
        }
        // Add scatter point
        return $series;
    }

}
