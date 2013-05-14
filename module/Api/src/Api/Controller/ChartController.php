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

        $questionnaires = $this->getEntityManager()->getRepository('Application\Model\Questionnaire')->findBy(array('geoname' => $country ? $country->getGeoname() : -1));

        $calculator = new \Application\Service\Calculator\Jmp();
        $calculator->setServiceLocator($this->getServiceLocator());

        $startYear = 1980;
        $endYear = 2011;

        // First get series of flatten regression lines
        $series = array();
        $filterCount = 0;
        if ($filterSet) {
            $filterCount = $filterSet->getFilters()->count();
            $series = $calculator->computeFlatten($startYear, $endYear, $filterSet, $questionnaires, $part);
            foreach ($series as &$serie) {
                $serie['type'] = 'line';
                foreach ($serie['data'] as &$d) {
                    if (!is_null($d))
                        $d = round($d * 100);
                }
            }

            // Then add scatter points which are each questionnaire values
            foreach ($filterSet->getFilters() as $filter) {
                $data = $calculator->computeFilterForAllQuestionnaires($filter, $questionnaires, $part);

                $scatter = array(
                    'type' => 'scatter',
                    'name' => $filter->getName(),
                    'data' => array(),
                );
                $i = 0;
                foreach ($data['values%'] as $surveyCode => $value) {

                    if (!is_null($value)) {
                        $scatter['data'][] = array(
                            'name' => $surveyCode,
                            'x' => $data['years'][$i],
                            'y' => round($value * 100, 1),
                        );
                    }
                    $i++;
                }
                $series[] = $scatter;
            }
        }



        $chart = array(
            'chart' => array(
                'height' => 600,
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
                        'format' => '{point.name}',
                    ),
                ),
            ),
            'series' => $series,
        );

        return new JsonModel($chart);
    }

}
