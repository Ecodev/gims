<?php

namespace Api\Controller;

use Application\View\Model\NumericJsonModel;
use Application\View\Model\ExcelModel;
use Application\Model\Geoname;
use Application\Utility;

class TableController extends \Application\Controller\AbstractAngularActionController
{

    private $parts; // used for cache

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

    public function filterAction()
    {
        $filterSet = $this->getEntityManager()->getRepository('Application\Model\FilterSet')->findOneById($this->params()->fromQuery('filterSet'));

        $result = array();
        if ($filterSet) {

            $idQuestionnaires = Utility::explodeIds($this->params()->fromQuery('questionnaire'));
            $questionnaireRepository = $this->getEntityManager()->getRepository('Application\Model\Questionnaire');
            $parts = $this->getEntityManager()->getRepository('Application\Model\Part')->findAll();

            foreach ($idQuestionnaires as $idQuestionnaire) {
                $questionnaire = $questionnaireRepository->find($idQuestionnaire);
                if ($questionnaire) {

                    // Do the actual computing for all filters
                    $resultOneQuestionnaire = array();
                    foreach ($filterSet->getFilters() as $filter) {
                        $resultOneQuestionnaire = array_merge($resultOneQuestionnaire, $this->computeWithChildren($questionnaire, $filter, $parts, 0, array('name')));
                    }

                    // Merge this questionnaire results with other questionnaire results
                    foreach ($resultOneQuestionnaire as $i => $data) {
                        if (isset($result[$i])) {
                            $result[$i]['values'][] = reset($data['values']);
                        } else {
                            $result[] = $data;
                        }
                    }
                }
            }
        }

        return new NumericJsonModel($result);
    }

    /**
     * Compute value for the given filter and all its children recursively.
     * @param \Application\Model\Questionnaire $questionnaire
     * @param \Application\Model\Filter $filter
     * @param array $parts
     * @param integer $level the level of the current filter in the filter tree
     * @param array $fields
     * @param array $overridenFilters
     * @param bool $useSecondStepRules
     * @param bool $roundValues
     * @return array a list (not tree) of all filters with their values and tree level
     */
    public function computeWithChildren(\Application\Model\Questionnaire $questionnaire, \Application\Model\Filter $filter, array $parts, $level = 0, $fields = array(), $overridenFilters = array(), $useSecondStepRules = false, $roundValues = true)
    {
        $calculator = new \Application\Service\Calculator\Calculator();
        $calculator->setServiceLocator($this->getServiceLocator());
        $calculator->setOverriddenFilters($overridenFilters);
        $hydrator = new \Application\Service\Hydrator();

        $current = array();
        $current['filter'] = $hydrator->extract($filter, $fields);
        $current['filter']['level'] = $level;

        foreach ($parts as $part) {
            $value = $calculator->computeFilter($filter->getId(), $questionnaire->getId(), $part->getId(), $useSecondStepRules, null);
            // Round the value
            if ($roundValues) {
                $value = \Application\Utility::decimalToRoundedPercent($value);
            }
            $current['values'][0][$part->getName()] = $value;
        }

        // Compute children
        $result = array($current);
        foreach ($filter->getChildren() as $child) {
            $result = array_merge($result, $this->computeWithChildren($questionnaire, $child, $parts, $level + 1, $fields, $overridenFilters, $useSecondStepRules, $roundValues));
        }

        return $result;
    }

    public function questionnaireAction()
    {
        $questionnairesIds = array_filter(explode(',', $this->params()->fromQuery('questionnaires')));
        $filtersIds = array_filter(explode(',', $this->params()->fromQuery('filters')));

        $questionnaires = $this->getEntityManager()->getRepository('Application\Model\Questionnaire')->findById($questionnairesIds);
        $questionnairesById = [];
        foreach ($questionnaires as $questionnaire) {
            $questionnairesById[$questionnaire->getId()] = $questionnaire;
        }
        $filters = $this->getEntityManager()->getRepository('Application\Model\Filter')->findById($filtersIds);
        $parts = $this->getEntityManager()->getRepository('Application\Model\Part')->findAll();
        $calculator = new \Application\Service\Calculator\Calculator();
        $calculator->setServiceLocator($this->getServiceLocator());

        $result = array();
        $columns = array(
            'country' => 'Country',
            'iso3' => 'ISO-3',
            'survey' => 'Survey',
            'year' => 'Year',
        );
        $legends = [];

        $allColumnNames = $this->getEntityManager()->getRepository('\Application\Model\Filter')->getColumnNames($filters, $parts);
        foreach ($parts as $part) {
            foreach ($filters as $filter) {
                $columnNames = $allColumnNames[$filter->getId()][$part->getId()];
                $columnId = 'f' . $filter->getId() . 'p' . $part->getId();
                $columns[$columnId] = $columnNames['short'];
                $legends[$columnId] = $columnNames;

                $data = $calculator->computeFilterForAllQuestionnaires($filter->getId(), $questionnaires, $part->getId());
                foreach ($data['values'] as $questionnaireId => $value) {
                    if (!isset($result[$questionnaireId])) {
                        $result[$questionnaireId] = array(
                            'country' => $questionnairesById[$questionnaireId]->getGeoname()->getName(),
                            'iso3' => $questionnairesById[$questionnaireId]->getGeoname()->getIso3(),
                            'survey' => $data['surveys'][$questionnaireId],
                            'year' => $data['years'][$questionnaireId],
                        );
                    }

                    $result[$questionnaireId][$columnId] = \Application\Utility::decimalToRoundedPercent($value);
                }
            }
        }

        $finalResult = array(
            'columns' => $columns,
            'legends' => array_values($legends),
            'data' => array_values($result),
        );

        $filename = $this->params('filename');
        if ($filename) {
            return new ExcelModel($filename, $finalResult);
        } else {
            return new NumericJsonModel($finalResult);
        }
    }

    public function countryAction()
    {
        $geonamesIds = array_filter(explode(',', $this->params()->fromQuery('geonames')));
        $filtersIds = array_filter(explode(',', $this->params()->fromQuery('filters')));
        $wantedYears = $this->getWantedYears($this->params()->fromQuery('years'));

        $geonames = $this->getEntityManager()->getRepository('Application\Model\Geoname')->findById($geonamesIds, array('name' => 'asc'));
        $filters = $this->getEntityManager()->getRepository('Application\Model\Filter')->findById($filtersIds);
        $parts = $this->getEntityManager()->getRepository('Application\Model\Part')->findAll();
        $populationRepository = $this->getEntityManager()->getRepository('Application\Model\Population');

        $result = array();
        $columns = array(
            'country' => 'Country',
            'iso3' => 'ISO-3',
            'year' => 'Year',
        );
        $legends = [];

        // Build population acronyms and add them to columns definition
        $populationAcronyms = array();
        $partsById = []; // used for cache
        foreach ($parts as $part) {
            $partsById[$part->getId()] = $part;
            $acronym = 'P' . strtoupper($part->getName()[0]);
            $populationAcronyms[$part->getId()] = $acronym;
            $columns[$acronym] = $acronym;
            $legends[] = [
                'short' => $acronym,
                'long' => 'Population, ' . $part->getName(),
            ];
        }

        $allColumnNames = $this->getEntityManager()->getRepository('\Application\Model\Filter')->getColumnNames($filters, $parts);
        foreach ($geonames as $geoname) {

            $allYearsComputed = $this->getAllYearsComputed($parts, $filters, $geoname);
            $filteredYearsComputed = $this->filterYears($allYearsComputed, $wantedYears);

            foreach ($wantedYears as $year) {

                // country info columns
                $countryData = array(
                    'country' => $geoname->getName(),
                    'iso3' => $geoname->getIso3(),
                    'year' => $year
                );

                // population columns
                $populationData = array();
                foreach ($parts as $part) {
                    $populationData[$populationAcronyms[$part->getId()]] = $populationRepository->getPopulationByGeoname($geoname, $part->getId(), $year);
                }

                $statsData = array();
                $count = 1;
                foreach ($filteredYearsComputed as $partId => $flatFilters) {

                    foreach ($flatFilters as $filter) {
                        $columnId = 'c' . $count;
                        $columnNames = $allColumnNames[$filter['id']][$partId];
                        $columns[$columnId] = $columnNames['short'];
                        $legends[$columnId] = $columnNames;

                        $value = $filter['data'][$year];
                        $statsData[$columnId] = \Application\Utility::decimalToRoundedPercent($value);

                        // Do absolute version
                        $columnId = $columnId . 'a';
                        $columnNames['short'] = $columnNames['short'] . 'a';
                        $columnNames['long'] = $columnNames['long'] . ' (absolute value)';
                        $legends[$columnId] = $columnNames;
                        $columns[$columnId] = $columnNames['short'];
                        $statsData[$columnId] = is_null($value) ? null : (int) ($value * $populationRepository->getPopulationByGeoname($geoname, $partId, $year));
                        $count++;
                    }
                }

                $result[] = array_merge($countryData, $populationData, $statsData);
            }
        }

        $finalResult = array(
            'columns' => $columns,
            'legends' => array_values($legends),
            'data' => array_values($result)
        );

        $filename = $this->params('filename');
        if ($filename) {
            return new ExcelModel($filename, $finalResult);
        } else {
            return new NumericJsonModel($finalResult);
        }
    }

    /**
     * @param \Application\Model\Part[] $parts
     * @param \Application\Model\Filter[] $filters
     * @param \Application\Model\Geoname $geoname
     * @return array all data ordered by part
     */
    private function getAllYearsComputed($parts, $filters, Geoname $geoname)
    {
        $aggregator = new \Application\Service\Calculator\Aggregator();
        $aggregator->setCalculator($this->getCalculator());

        $dataPerPart = array();
        foreach ($parts as $part) {
            $dataPerPart[$part->getId()] = $aggregator->computeFlattenAllYears($filters, $geoname, $part);
        }

        return $dataPerPart;
    }

    /**
     * @param $fieldParts
     * @param array $wantedYears
     * @return array Filter ordered by part and with only wanted years.
     */
    private function filterYears($fieldParts, array $wantedYears)
    {
        $finalFieldsets = array();
        foreach ($fieldParts as $partId => $filters) {
            $finalFieldsets[$partId] = array();
            foreach ($filters as $filter) {
                $yearsData = array();
                foreach ($wantedYears as $year) {
                    $yearsData[$year] = $filter['data'][$year - 1980];
                }
                $tmpFieldset = array(
                    'name' => $filter['name'],
                    'id' => $filter['id'],
                    'data' => $yearsData
                );
                $finalFieldsets[$partId][] = $tmpFieldset;
            }
        }

        return $finalFieldsets;
    }

    /**
     * Decode the syntax of wanted years
     * @param $years
     * @return array of years
     */
    private function getWantedYears($years)
    {
        $ranges = explode(',', $years);
        $finalYears = [];
        foreach ($ranges as $range) {
            $range = trim($range, ' ');
            if (!strpos($range, '-')) {
                $finalYears[] = (int) $range;
            } else {
                $startAndEndYear = explode('-', $range);
                $finalYears = array_merge($finalYears, range($startAndEndYear[0], $startAndEndYear[1]));
            }
        }
        $finalYears = array_filter(array_unique($finalYears));
        sort($finalYears);

        return $finalYears;
    }

}
