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
                $hFilters = $filterSet->getFilters()->map(function ($el) {
                    return $el->getId();
                });
                $this->usedFilters = array_merge($this->usedFilters, $hFilters->toArray());
            }
        }
        $filterSetsName = trim($filterSetsName, ", ");

        $part = $this->getEntityManager()->getRepository('Application\Model\Part')->findOneById($this->params()->fromQuery('part'));

        $questionnaires = $this->getEntityManager()->getRepository('Application\Model\Questionnaire')->getByGeonameWithSurvey($country ? $country->getGeoname() : -1);

        $this->startYear = 1980;
        $this->endYear = 2012;

        $series = array();
        if (count($filterSets) > 0) {

            // First get series of flatten regression lines with ignored values (if any)
            $seriesWithIgnoredElements = $this->computeIgnoredElements($questionnaires, $part);

            foreach ($filterSets as $filterSet) {

                // If the filterSet is a copy of an original FilterSet, then we also display the original (with light colors)
                if ($filterSet->getOriginalFilterSet()) {
                    $originalFilterSet = $filterSet->getOriginalFilterSet();
                    $seriesWithOriginal = $this->getSeries($originalFilterSet, $questionnaires, $part, array(), 100, null, false, ' (original)');
                } else {
                    $seriesWithOriginal = array();
                }

                $ignoredFilters = array();
                foreach ($filterSet->getExcludedFilters() as $ignoredFilter) {
                    $ignoredFilters[] = $ignoredFilter->getId();
                }

                // Finally we compute "normal" series, and make it "light" if we have alternative series to highlight
                $alternativeSeries = array_merge($seriesWithIgnoredElements, $seriesWithOriginal);
                $normalSeries = $this->getSeries($filterSet, $questionnaires, $part, array('byFilterSet' => $ignoredFilters), $alternativeSeries ? 33 : 100, $alternativeSeries ? 'ShortDash' : null, false);

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
     * Returns all series for ignored questionnaires AND filters at the same time
     * @param array $questionnaires
     * @param \Application\Model\Part $part
     * @return array
     */
    protected function computeIgnoredElements(array $questionnaires, Part $part)
    {
        $ignoredFiltersByQuestionnaire = $this->getIgnoredElements();

        $series = array();
        if (count($ignoredFiltersByQuestionnaire['byQuestionnaire']) > 0) {
            foreach ($this->usedFilters as $filterId) {

                $filter = $this->getEntityManager()->getRepository('Application\Model\Filter')->findOneById($filterId);
                $filterSetSingle = new \Application\Model\FilterSet();
                $filterSetSingle->addFilter($filter);

                $questionnairesNotIgnored = array();
                foreach ($questionnaires as $questionnaire) {
                    // if questionnaire is not in the ignored list, add to not ignored questionnaires list
                    // or if questionnaire is in the list ignored list but has filters, he's added to not ignored questionnaires list
                    // (a questionnaire is considered ignored if he's in the list AND he has not filters. If he has filters, they are ignored, but not the questionnaire)
                    if (!isset($ignoredFiltersByQuestionnaire['byQuestionnaire'][$questionnaire->getId()])
                        || isset($ignoredFiltersByQuestionnaire['byQuestionnaire'][$questionnaire->getId()])
                        && count($ignoredFiltersByQuestionnaire['byQuestionnaire'][$questionnaire->getId()]) > 0
                    ) {
                        $questionnairesNotIgnored[] = $questionnaire;
                    }
                }

                $mySeries = $this->getSeries($filterSetSingle, $questionnairesNotIgnored, $part, $ignoredFiltersByQuestionnaire, 100, null, true, ' (ignored elements)');
                $series = array_merge($series, $mySeries);
            }
        }

        return $series;
    }

    /**
     * Retrieve ignored elements and return un associative array where
     * key is questionnaire Id and value is a list of ignored filters
     * @return array
     */
    public function getIgnoredElements()
    {
        $excludeStr = $this->params()->fromQuery('ignoredElements');
        $ignoredElements = $excludeStr ? explode(',', $excludeStr) : array();

        $ignoredFiltersByQuestionnaire = array();
        foreach ($ignoredElements as $ignoredQuestionnaire) {
            @list($questionnaireId, $filters) = explode(':', $ignoredQuestionnaire);
            $filters = $filters ? explode('-', $filters) : $filters = array();
            $ignoredFiltersByQuestionnaire[$questionnaireId] = $filters;
        }

        return array('byQuestionnaire' => $ignoredFiltersByQuestionnaire);
    }

    /**
     * Get line and scatter series for the given filterSet and questionnaires
     * @param \Application\Model\FilterSet $filterSet
     * @param array $questionnaires
     * @param \Application\Model\Part $part
     * @param array $ignoredFilters
     * @param $ratio
     * @param string $dashStyle
     * @param string $suffix for serie name
     * @internal param array $colors
     * @return string
     */
    protected function getSeries(FilterSet $filterSet, array $questionnaires, Part $part, array $ignoredFilters, $ratio, $dashStyle = null, $isIgnored = false, $suffix = null)
    {
        //echo '(ratio ' . $ratio . ' - )';
        $series = array();
        $calculator = new \Application\Service\Calculator\Jmp();
        $calculator->setServiceLocator($this->getServiceLocator());
        $lines = $calculator->computeFlattenAllYears($this->startYear, $this->endYear, $filterSet, $questionnaires, $part, $ignoredFilters);
        $filterRepository = $this->getEntityManager()->getRepository('Application\Model\Filter');
        foreach ($lines as &$serie) {
            $filter = $filterRepository->findOneById($serie['id']);
            $serie['color'] = $filter->getGenericColor($ratio);
            $serie['name'] .= $suffix;
            $serie['isIgnored'] = $isIgnored;

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
                'color' => $filter->getGenericColor($ratio),
                'marker' => array('symbol' => $this->symbols[$this->getConstantKey($filter->getName()) % count($this->symbols)]),
                'name' => $filter->getName() . $suffix,
                'allowPointSelect' => false,
                'isIgnored' => $isIgnored,
                // because we will use our own click handler
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

            /** @var \Application\Service\Calculator\Calculator $calculator */
            $calculator = new \Application\Service\Calculator\Calculator();
            $calculator->setServiceLocator($this->getServiceLocator());

            /** @var  \Application\Service\Hydrator $hydrator */
            $hydrator = new \Application\Service\Hydrator();

            if ($this->params()->fromQuery('getQuestionnaireUsages') === 'true') {
                if ($usages = $this->extractUsages($questionnaire->getQuestionnaireUsages(), null, $part, $hydrator, $calculator)) {
                    $result['usages'] = $usages;
                }
            }

            /**
             * This call recovers ignored questionnaires and filters.
             * In order to follow the logic : panel displays info to understand chart,
             * If the questionnaire requested is ignored all filters should be null (cause no value is used) on chart
             */
            $ignoredFiltersByQuestionnaire = $this->getIgnoredElements();
            if (isset($ignoredFiltersByQuestionnaire['byQuestionnaire']) // if there are ignored questionnaire
                && !isset($ignoredFiltersByQuestionnaire['byQuestionnaire'][$questionnaire->getId()]) // and if questionnaire is not ignored
                || isset($ignoredFiltersByQuestionnaire['byQuestionnaire'][$questionnaire->getId()]) // or if he's ignored but he has filters (that means he's not ignored himself, but some filters are)
                && count($ignoredFiltersByQuestionnaire['byQuestionnaire'][$questionnaire->getId()]) > 0
            ) {
                // compute filters
                foreach ($filters as $filterId) {
                    $filter = $this->getEntityManager()->getRepository('Application\Model\Filter')->findOneById($filterId);
                    $fields = explode(',', $this->params()->fromQuery('fields'));

                    $tableController = new TableController();
                    $tableController->setServiceLocator($this->getServiceLocator());

                    $result['filters'][$filterId] = $tableController->computeWithChildren($questionnaire, $filter, array($part), 0, $fields, array(), true);
                    $resultWithoutIgnoredFilters['filters'][$filterId] = $tableController->computeWithChildren($questionnaire, $filter, array($part), 0, $fields, $ignoredFiltersByQuestionnaire, true);

                    for ($i = 0; $i < count($result['filters'][$filterId]); $i++) {
                        if ($result['filters'][$filterId][$i]['values'][0][$part->getName()] != $resultWithoutIgnoredFilters['filters'][$filterId][$i]['values'][0][$part->getName()]) {
                            $result['filters'][$filterId][$i]['valuesWithoutIgnored'] = $resultWithoutIgnoredFilters['filters'][$filterId][$i]['values'];
                        }
                    }

                    foreach ($result['filters'][$filterId] as &$flatFilter) {
                        $fqus = $this->getEntityManager()->getRepository('Application\Model\Filter')->findOneById($flatFilter['filter']['id'])->getFilterQuestionnaireUsages();
                        foreach($fqus as $fqu){
                            if ($fqu->getPart() === $part && $fqu->getQuestionnaire() === $questionnaire){
                                if (!isset($flatFilter['usages'])) {
                                    $flatFilter['usages'] = $fqu->getRule()->getName();
                                } else {
                                    $flatFilter['usages'] .= ', '.$fqu->getRule()->getName();
                                }

                            }
                        }
                    }
                }
            }

            return new NumericJsonModel($result);

        } else {
            $this->getResponse()->setStatusCode(404);

            return new NumericJsonModel(array('message' => 'questionnaire not found'));
        }
    }

    protected function extractUsages($usages, $questionnaire = null, $part, $hydrator, $calculator)
    {
        $extractedUsages = array();
        foreach ($usages as $usage) {
            if ($usage->getPart() === $part
                && (!$questionnaire || $questionnaire && $usage->getQuestionnaire() === $questionnaire)
            ) {
                $extractedUsage = $hydrator->extract($usage, array(
                    'part',
                    'rule',
                    'rule.name',
                ));
                $value = $calculator->computeFormula($usage);
                $value = \Application\Utility::decimalToRoundedPercent($value);
                $extractedUsage['value'] = $value;
                $extractedUsages[] = $extractedUsage;
            }
        }

        return $extractedUsages;
    }

    /**
     * Generates a filterset, a filter and a survey by year with a question associated to a the filter and to answers
     * @return NumericJsonModel
     */
    public function generateFilterAction()
    {
        $name = $this->params()->fromQuery('name');
        $color = $this->params()->fromQuery('color');
        $surveys = explode(',', $this->params()->fromQuery('surveys'));

        $existingSurvey = $this->getEntityManager()->getRepository('Application\Model\Survey')->findOneByName($name);
        $existingFilter = $this->getEntityManager()->getRepository('Application\Model\Filter')->findOneByName($name);
        $existingFilterSet = $this->getEntityManager()->getRepository('Application\Model\FilterSet')->findOneByName($name);

        if ($existingSurvey || $existingFilter || $existingFilterSet) {
            return new NumericJsonModel(array('message' => 'name "' . $name . '" already used'));
        }

        $user = User::getCurrentUser();

        /** get roles */
        $roleEditor = $this->getEntityManager()->getRepository('Application\Model\Role')->findOneByName('editor');
        $roleReporter = $this->getEntityManager()->getRepository('Application\Model\Role')->findOneByName('reporter');

        /** @var \Application\Model\Part $part */
        $part = $this->getEntityManager()->getRepository('Application\Model\Part')->findOneById($this->params()->fromQuery('part'));
        $parts = new ArrayCollection();
        $parts->add($part);

        /** @var \Application\Model\Country $country */
        $country = $this->getEntityManager()->getRepository('Application\Model\Country')->findOneById($this->params()->fromQuery('country'));

        /** @var \Application\Model\Geoname $geoname */
        $geoname = $country->getGeoname();

        /** @var \Application\Model\FilterSet $filterSet */
        $filterSet = new FilterSet();
        $filterSet->setName($name);
        $this->getEntityManager()->persist($filterSet);

        /** @var \Application\Model\Filter $filter */
        $filter = new Filter();
        $filter->setName($name);
        $filter->setColor($color);
        $filterSet->addFilter($filter);
        $this->getEntityManager()->persist($filter);

        foreach ($surveys as $s) {

            list($year, $value) = explode(':', $s);

            /** @var \Application\Model\Survey $survey */
            $survey = new Survey();
            $survey->setCode($name . ' ' . $year);
            $survey->setName($name . ' ' . $year);
            $survey->setYear($year);
            $survey->setIsActive(1);
            $this->getEntityManager()->persist($survey);

            /** @var \Application\Model\UserSurvey $userSurvey */
            $userSurvey = new UserSurvey();
            $userSurvey->setUser($user);
            $userSurvey->setSurvey($survey);
            $userSurvey->setRole($roleEditor);
            $this->getEntityManager()->persist($userSurvey);

            /** @var \Application\Model\NumericQuestion $question */
            $question = new NumericQuestion();
            $question->setName($name);
            $question->setFilter($filter);
            $question->setSurvey($survey);
            $question->setParts($parts);
            $question->setSorting(1);
            $question->setIsPopulation(true);
            $question->setIsCompulsory(true);
            $this->getEntityManager()->persist($question);

            /** @var \Application\Model\Questionnaire $questionnaire */
            $questionnaire = new Questionnaire();
            $questionnaire->setSurvey($survey);
            $questionnaire->setGeoname($geoname);
            $questionnaire->setDateObservationStart(new \DateTime($year . '-01-01'));
            $questionnaire->setDateObservationEnd(new \DateTime($year . '-12-31'));
            $this->getEntityManager()->persist($questionnaire);

            /** @var \Application\Model\UserQuestionnaire $userQuestionnaire */
            $userQuestionnaire = new UserQuestionnaire();
            $userQuestionnaire->setUser($user);
            $userQuestionnaire->setQuestionnaire($questionnaire);
            $userQuestionnaire->setRole($roleEditor);
            $this->getEntityManager()->persist($userQuestionnaire);
            $this->getEntityManager()->persist($questionnaire);

            /** @var \Application\Model\UserQuestionnaire $userQuestionnaire */
            $userQuestionnaire = new UserQuestionnaire();
            $userQuestionnaire->setUser($user);
            $userQuestionnaire->setQuestionnaire($questionnaire);
            $userQuestionnaire->setRole($roleReporter);
            $this->getEntityManager()->persist($userQuestionnaire);

            /** @var \Application\Model\Population $population */
            $population = $this->getEntityManager()->getRepository('Application\Model\Population')->getOneByGeoname($geoname, $part->getId(), $year);

            /** @var \Application\Model\Answer $answer */
            $answer = new Answer();
            $answer->setQuestion($question);
            $answer->setQuestionnaire($questionnaire);
            $answer->setPart($part);
            $answer->setValuePercent($value);
            $answer->setValueAbsolute($value * $population->getPopulation());
            $this->getEntityManager()->persist($answer);
        }

        $this->getEntityManager()->flush();
        $this->getResponse()->setStatusCode(201);

        $hydrator = new \Application\Service\Hydrator();

        return new NumericJsonModel($hydrator->extract($filterSet));
    }

}
