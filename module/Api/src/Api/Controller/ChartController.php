<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class ChartController extends \Application\Controller\AbstractAngularActionController
{

    public function indexAction()
    {
        $questionnaires = $this->getEntityManager()->getRepository('Application\Model\Questionnaire')->findAll();
        $filter = $this->getEntityManager()->getRepository('Application\Model\Filter')->findOneBy(array('name' => $this->params()->fromQuery('filter', 'Water')));
        $part = $this->getEntityManager()->getRepository('Application\Model\Part')->findOneBy(array('name' => $this->params()->fromQuery('part', 'Urban')));

        $calculator = new \Application\Service\Calculator\Jmp();
        $calculator->setServiceLocator($this->getServiceLocator());

        $startYear = 1980;
        $endYear = 2011;

        // First get series of flatten regression lines
        $series = $calculator->computeFlatten($startYear, $endYear, $questionnaires, $filter, $part);
        foreach ($series as &$serie) {
            $serie['type'] = 'spline';
            foreach ($serie['data'] as &$d) {
                if (!is_null($d))
                    $d = round($d * 100);
            }
        }

        // Then add scatter points which are each questionnaire values
        foreach ($filter->getCategoryFilterComponents() as $filterComponent) {
            $data = $calculator->computeFilter($questionnaires, $filterComponent, $part);

            $scatter = array(
                'type' => 'scatter',
                'name' => $filterComponent->getName(),
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



        $chart = array(
            'chart' => array(
                'height' => 600,
            ),
            // Here we use default highchart colors and symbols, but truncated at the same number of series,
            // so it will get repeated for lines and scatter points
            'colors' => array_slice(array('#2f7ed8', '#0d233a', '#8bbc21', '#910000', '#1aadce', '#492970', '#f28f43', '#77a1e5', '#c42525', '#a6c96a'), 0, $filter->getCategoryFilterComponents()->count()),
            'symbols' => array_slice(array('circle', 'diamond', 'square', 'triangle', 'triangle-down'), 0, $filter->getCategoryFilterComponents()->count()),
            'title' => array(
                'text' => 'Bangladesh - ' . ($part ? $part->getName() : 'Total'),
            ),
            'subtitle' => array(
                'text' => 'JMP - estimated proportion of the population using improved drinking water sources',
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
                'spline' => array(
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
                    'lineWidth' => 1,
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
