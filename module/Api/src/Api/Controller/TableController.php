<?php

namespace Api\Controller;

use Application\Model\Geoname;
use Application\Utility;
use Application\View\Model\ExcelModel;
use Application\View\Model\NumericJsonModel;
use Zend\View\Model\JsonModel;

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

        $result = [];
        if ($filterSet) {

            $idQuestionnaires = Utility::explodeIds($this->params()->fromQuery('questionnaire'));
            $questionnaireRepository = $this->getEntityManager()->getRepository('Application\Model\Questionnaire');
            $parts = $this->getEntityManager()->getRepository('Application\Model\Part')->findAll();

            foreach ($idQuestionnaires as $idQuestionnaire) {
                $questionnaire = $questionnaireRepository->find($idQuestionnaire);
                if ($questionnaire) {

                    // Do the actual computing for all filters
                    $resultOneQuestionnaire = [];
                    foreach ($filterSet->getFilters() as $filter) {
                        $resultOneQuestionnaire = array_merge($resultOneQuestionnaire, $this->computeWithChildren($questionnaire, $filter, $parts, 0, ['name']));
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
    public function computeWithChildren(\Application\Model\Questionnaire $questionnaire, \Application\Model\Filter $filter, array $parts, $level = 0, $fields = [], $overridenFilters = [], $useSecondStepRules = false, $roundValues = true)
    {
        $calculator = new \Application\Service\Calculator\Calculator();
        $calculator->setServiceLocator($this->getServiceLocator());
        $calculator->setOverriddenFilters($overridenFilters);
        $hydrator = new \Application\Service\Hydrator();

        $current = [];
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
        $result = [$current];
        foreach ($filter->getChildren() as $child) {
            $result = array_merge($result, $this->computeWithChildren($questionnaire, $child, $parts, $level + 1, $fields, $overridenFilters, $useSecondStepRules, $roundValues));
        }

        return $result;
    }

    public function questionnaireAction()
    {
        $questionnairesIds = Utility::explodeIds($this->params()->fromQuery('questionnaires'));
        $filtersIds = Utility::explodeIds($this->params()->fromQuery('filters'));

        $questionnaires = Utility::orderByIds($this->getEntityManager()->getRepository('Application\Model\Questionnaire')->findById($questionnairesIds), $questionnairesIds);
        $questionnairesById = Utility::indexById($questionnaires);
        $filters = Utility::orderByIds($this->getEntityManager()->getRepository('Application\Model\Filter')->findById($filtersIds), $filtersIds);
        $parts = $this->getEntityManager()->getRepository('Application\Model\Part')->findAll();
        $calculator = new \Application\Service\Calculator\Calculator();
        $calculator->setServiceLocator($this->getServiceLocator());

        $result = [];
        $columns = [
            ['field' => 'country', 'displayName' => 'Country', 'width' => 120],
            ['field' => 'survey', 'displayName' => 'Code', 'width' => 100],
            ['field' => 'surveyName', 'displayName' => 'Survey', 'width' => 250],
            ['field' => 'year', 'displayName' => 'Year', 'width' => 60],
        ];

        $columns = array_merge($columns, $this->getEntityManager()
                        ->getRepository('\Application\Model\Filter')
                        ->getColumnNames($filters, $parts));
        foreach ($parts as $part) {
            foreach ($filters as $filter) {

                $data = $calculator->computeFilterForAllQuestionnaires($filter->getId(), $questionnaires, $part->getId());
                foreach ($data['values'] as $questionnaireId => $value) {
                    if (!isset($result[$questionnaireId])) {
                        $result[$questionnaireId] = [
                            'country' => $questionnairesById[$questionnaireId]->getGeoname()->getName(),
                            'survey' => $data['surveys'][$questionnaireId]['code'],
                            'surveyName' => $data['surveys'][$questionnaireId]['name'],
                            'year' => $data['years'][$questionnaireId],
                        ];
                    }

                    $result[$questionnaireId]['f' . $filter->getId() . 'p' . $part->getId()] = \Application\Utility::decimalToRoundedPercent($value);
                }
            }
        }

        $finalResult = [
            'columns' => $columns,
            'data' => array_values($result),
        ];

        $filename = $this->params('filename');
        if ($filename) {
            return new ExcelModel($filename, $finalResult);
        } else {
            return new JsonModel($finalResult);
        }
    }

    public function countryAction()
    {
        $geonamesIds = Utility::explodeIds($this->params()->fromQuery('geonames'));
        $filtersIds = Utility::explodeIds($this->params()->fromQuery('filters'));
        $wantedYears = $this->getWantedYears($this->params()->fromQuery('years'));
        $excelFileName = $this->params('filename');

        $geonames = $this->getEntityManager()->getRepository('Application\Model\Geoname')->findById($geonamesIds, ['name' => 'asc']);
        $filters = $this->getEntityManager()->getRepository('Application\Model\Filter')->findById($filtersIds);
        $parts = $this->getEntityManager()->getRepository('Application\Model\Part')->findAll();
        $populationRepository = $this->getEntityManager()->getRepository('Application\Model\Population');
        $questionnaireRepository = $this->getEntityManager()->getRepository('Application\Model\Questionnaire');

        // Pre-fill cache for things we know that we will need very soon
        $populationRepository->fillPopulationCache($geonames);
        $questionnaireRepository->fillCacheForComputing($geonames);

        // Re-order filters to be the same order as selection in GUI
        $filters = Utility::orderByIds($filters, $filtersIds);

        $result = [];
        $columns = [
            ['field' => 'country', 'displayName' => 'Country', 'width' => 120],
            ['field' => 'iso3', 'displayName' => 'ISO3', 'width' => 60],
            ['field' => 'year', 'displayName' => 'Year', 'width' => 60],
        ];

        // Build population acronyms and add them to columns definition
        foreach ($parts as $part) {
            $acronym = 'P' . strtoupper($part->getName()[0]);
            $columns[] = [
                'field' => $acronym,
                'displayLong' => $part->getName() . ' Population',
                'displayName' => $acronym,
                'width' => 90,
            ];
        }

        $cols = $this->getEntityManager()->getRepository('\Application\Model\Filter')->getColumnNames($filters, $parts);
        foreach ($cols as $column) {
            $columns[] = $column;
            $column['field'] .= 'a';
            $column['displayName'] .= 'A';
            $column['displayLong'] .= ' (absolute value)';
            $columns[] = $column;
        }

        foreach ($geonames as $geoname) {
            $allYearsComputed = $this->getAllYearsComputed($parts, $filters, $geoname);
            $filteredYearsComputed = $this->filterYears($allYearsComputed, $wantedYears);

            foreach ($wantedYears as $year) {

                // country info columns
                $countryData = [
                    'country' => $geoname->getName(),
                    'iso3' => $geoname->getIso3(),
                    'year' => $year,
                ];

                // population columns
                $populationData = [];
                foreach ($parts as $part) {
                    $populationData['P' . strtoupper($part->getName()[0])] = $this->formatThousand($populationRepository->getPopulationByGeoname($geoname, $part->getId(), $year), $excelFileName);
                }

                $statsData = [];
                foreach ($filteredYearsComputed as $partId => $flatFilters) {
                    foreach ($flatFilters as $filter) {
                        $value = $filter['data'][$year];
                        $statsData['f' . $filter['id'] . 'p' . $partId] = \Application\Utility::decimalToRoundedPercent($value);
                        $statsData['f' . $filter['id'] . 'p' . $partId . 'a'] = is_null($value) ? null : $this->formatThousand($value * $populationRepository->getPopulationByGeoname($geoname, $partId, $year), $excelFileName);
                    }
                }

                $result[] = array_merge($countryData, $populationData, $statsData);
            }
        }

        $finalResult = [
            'columns' => $columns,
            'data' => array_values($result),
        ];

        if ($excelFileName) {
            return new ExcelModel($excelFileName, $finalResult);
        } else {
            return new JsonModel($finalResult);
        }
    }

    /**
     * Format number with thousand separator, but only for non-Excel ouput
     * @param float $number
     * @param boolean $isExcel
     * @return integer|string
     */
    private function formatThousand($number, $isExcel)
    {
        if ($isExcel) {
            return round($number);
        } else {
            $formatted = number_format($number, 0, ".", " ");
            if ($formatted == '-0') {
                $formatted = '0';
            }

            return $formatted;
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

        $dataPerPart = [];
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
        $finalFieldsets = [];
        foreach ($fieldParts as $partId => $filters) {
            $finalFieldsets[$partId] = [];
            foreach ($filters as $filter) {
                $yearsData = [];
                foreach ($wantedYears as $year) {
                    $yearsData[$year] = $filter['data'][$year - 1980];
                }
                $tmpFieldset = [
                    'name' => $filter['name'],
                    'id' => $filter['id'],
                    'data' => $yearsData,
                ];
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
