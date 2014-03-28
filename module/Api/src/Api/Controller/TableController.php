<?php

namespace Api\Controller;

use Application\View\Model\NumericJsonModel;
use Application\View\Model\ExcelModel;

class TableController extends \Application\Controller\AbstractAngularActionController
{

    private $parts; // used for cache

    public function filterAction()
    {
        $questionnaireParameter = $this->params()->fromQuery('questionnaire');
        $idQuestionnaires = explode(',', $questionnaireParameter);
        $questionnaireRepository = $this->getEntityManager()->getRepository('Application\Model\Questionnaire');

        $parts = $this->getEntityManager()->getRepository('Application\Model\Part')->findAll();

        $filterSet = $this->getEntityManager()->getRepository('Application\Model\FilterSet')->findOneById($this->params()->fromQuery('filterSet'));

        $result = array();
        if ($filterSet) {
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
     *
     * @param \Application\Model\Questionnaire $questionnaire
     * @param \Application\Model\Filter $filter
     * @param array $parts
     * @param integer $level the level of the current filter in the filter tree
     * @param array $fields
     * @param array $ignoredElementsByQuestionnaire
     * @return array a list (not tree) of all filters with their values and tree level
     */
    public function computeWithChildren(\Application\Model\Questionnaire $questionnaire, \Application\Model\Filter $filter, array $parts, $level = 0, $fields = array(), $ignoredElementsByQuestionnaire = array(), $userSecondLevelRules = false)
    {
        $calculator = new \Application\Service\Calculator\Calculator();
        $calculator->setServiceLocator($this->getServiceLocator());
        $hydrator = new \Application\Service\Hydrator();

        $current = array();
        $current['filter'] = $hydrator->extract($filter, $fields);
        $current['filter']['level'] = $level;

        foreach ($parts as $part) {
            $computed = $calculator->computeFilter($filter->getId(), $questionnaire->getId(), $part->getId(), $userSecondLevelRules, null, $ignoredElementsByQuestionnaire);
            // Round the value
            $value = \Application\Utility::decimalToRoundedPercent($computed);
            $current['values'][0][$part->getName()] = $value;
        }

        // Compute children
        $result = array($current);
        foreach ($filter->getChildren() as $child) {
            $result = array_merge($result, $this->computeWithChildren($questionnaire, $child, $parts, $level + 1, $fields, $ignoredElementsByQuestionnaire, $userSecondLevelRules));
        }

        return $result;
    }

    public function questionnaireAction()
    {
        $p = $this->params()->fromQuery('country');
        if (!$p) {
            $countryIds = array(-1);
        } else {
            $countryIds = explode(',', $p);
        }
        $countries = $this->getEntityManager()->getRepository('Application\Model\Country')->findById($countryIds);

        /** @var \Application\Model\FilterSet $filterSet */
        $filterSet = $this->getEntityManager()->getRepository('Application\Model\FilterSet')->findOneById($this->params()->fromQuery('filterSet'));
        $parts = $this->getEntityManager()->getRepository('Application\Model\Part')->findAll();
        $calculator = new \Application\Service\Calculator\Jmp();
        $calculator->setServiceLocator($this->getServiceLocator());

        $result = array();
        $columns = array(
            'country' => 'Country',
            'iso3' => 'ISO-3',
            'survey' => 'Survey',
            'year' => 'Year',
        );

        foreach ($countries as $country) {
            $questionnaires = $this->getEntityManager()->getRepository('Application\Model\Questionnaire')->getByGeonameWithSurvey($country->getGeoname());
            if (!$filterSet) {
                continue;
            }

            foreach ($parts as $part) {
                foreach ($filterSet->getFilters() as $filter) {
                    $columnName = $this->getCodeName($filterSet, $part, $filter->getName());
                    $columnId = 'f' . $filter->getId() . 'p' . $part->getId();
                    $columns[$columnId] = $columnName;

                    $data = $calculator->computeFilterForAllQuestionnaires($filter->getId(), $questionnaires, $part->getId());
                    foreach ($data['values'] as $questionnaireId => $value) {
                        if (!isset($result[$questionnaireId])) {
                            $result[$questionnaireId] = array(
                                'country' => $country->getName(),
                                'iso3' => $country->getIso3(),
                                'survey' => $data['surveys'][$questionnaireId],
                                'year' => $data['years'][$questionnaireId],
                            );
                        }

                        $result[$questionnaireId][$columnId] = \Application\Utility::decimalToRoundedPercent($value);
                    }
                }
            }
        }
        $finalResult = array(
            'columns' => $columns,
            'data' => array_values($result),
        );

        $filename = $this->params('filename');
        if ($filename)
            return new ExcelModel($filename, $finalResult);
        else
            return new NumericJsonModel($finalResult);
    }

    public function countryAction()
    {
        $p = $this->params()->fromQuery('country');
        $years = $this->getWantedYears($this->params()->fromQuery('years'));
        if (!$p)
            $countryIds = array(-1);
        else
            $countryIds = explode(',', $p);
        $countries = $this->getEntityManager()->getRepository('Application\Model\Country')->findById($countryIds);

        /** @var \Application\Model\FilterSet $filterSet */
        $filterSet = $this->getEntityManager()->getRepository('Application\Model\FilterSet')->findOneById($this->params()->fromQuery('filterSet'));
        $parts = $this->getEntityManager()->getRepository('Application\Model\Part')->findAll();
        $populationRepository = $this->getEntityManager()->getRepository('Application\Model\Population');

        $this->parts = $parts; // used for cache

        $result = array();
        $columns = array(
            'country' => 'Country',
            'iso3' => 'ISO-3',
            'year' => 'Year',
        );

        // Build population acronyms and add them to columns definition
        $populationAcronyms = array();
        foreach ($parts as $part) {
            $acronym = 'P' . strtoupper($part->getName()[0]);
            $populationAcronyms[$part->getId()] = $acronym;
            $columns[$acronym] = $acronym;
        }

        foreach ($countries as $country) {

            $questionnaires = $this->getEntityManager()->getRepository('Application\Model\Questionnaire')->getByGeonameWithSurvey($country->getGeoname());
            if (!$filterSet) {
                continue;
            }

            $allYearsComputed = $this->getAllYearsComputed($parts, $filterSet, $questionnaires);
            $filteredYearsComputed = $this->filterYears($allYearsComputed, $years);

            foreach ($years as $year) {

                // country info columns
                $countryData = array(
                    'country' => $country->getName(),
                    'iso3' => $country->getIso3(),
                    'year' => $year
                );

                // population columns
                $populationData = array();
                foreach ($parts as $part) {
                    $pop = $populationRepository->getOneByGeoname($country->getGeoname(), $part->getId(), $year);
                    $populationData[$populationAcronyms[$part->getId()]] = $pop->getPopulation();
                }

                $statsData = array();
                $count = 1;
                foreach ($filteredYearsComputed as $partId => $filters) {

                    foreach ($filters as $filter) {
                        $columnId = 'c' . $count;
                        $columnName = $this->getCodeName($filterSet, $partId, $filter['name']);
                        $columns[$columnId] = $columnName;

                        $value = $filter['data'][$year];
                        $statsData[$columnId] = \Application\Utility::decimalToRoundedPercent($value);

                        $columns[$columnId . 'a'] = $columnName . 'a';
                        $statsData[$columnId . 'a'] = is_null($value) ? null : (int) ($value * $populationRepository->getOneByGeoname($country->getGeoname(), $partId, $year)->getPopulation());
                        $count++;
                    }
                }

                $result[] = array_merge($countryData, $populationData, $statsData);
            }
        }

        $finalResult = array(
            'columns' => $columns,
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
     * @param $parts
     * @param $filterSet
     * @param $questionnaires
     *
     * @return array all data ordered by part
     */
    private function getAllYearsComputed($parts, $filterSet, $questionnaires)
    {
        $calculator = new \Application\Service\Calculator\Jmp();
        $calculator->setServiceLocator($this->getServiceLocator());

        $dataPerPart = array();
        foreach ($parts as $part) {
            $dataPerPart[$part->getId()] = $calculator->computeFlattenAllYears(1980, 2015, $filterSet, $questionnaires, $part);
        }

        return $dataPerPart;
    }

    /**
     * @param $fieldParts
     * @param $years
     *
     * @return array Filter ordered by part and with only wanted years.
     */
    private function filterYears($fieldParts, $years)
    {
        $finalFieldsets = array();
        foreach ($fieldParts as $partId => $filters) {
            $finalFieldsets[$partId] = array();
            foreach ($filters as $filter) {
                $yearsData = array();
                foreach ($years as $year) {
                    $yearsData[$year] = $filter['data'][$year - 1980];
                }
                $tmpFieldset = array(
                    'name' => $filter['name'],
                    'data' => $yearsData
                );
                $finalFieldsets[$partId][] = $tmpFieldset;
            }
        }

        return $finalFieldsets;
    }

    /**
     * Decode the syntax of wanted years
     *
     * @param $years
     *
     * @return array of years
     */
    private function getWantedYears($years)
    {
        $ranges = explode(',', $years);
        $finalYears = [];
        foreach ($ranges as $range) {
            $range = trim($range, ' ');
            if (!strpos($range, '-')) {
                $finalYears[] = $range;
            } else {
                $startAndEndYear = explode('-', $range);
                $finalYears = array_merge($finalYears, range($startAndEndYear[0], $startAndEndYear[1]));
            }
        }
        $finalYears = array_unique($finalYears);
        sort($finalYears);

        return $finalYears;
    }

    /**
     * Retreive a code name by using two techniques : manual mapping or inversed acronym letters for the filter
     *
     * @param $filterset
     * @param $partId
     * @param $filterName
     *
     * @return string code name in uppercase
     */
    public function getCodeName($filterset, $part, $filterName)
    {
        // first letter of the Filterset (care, if some have W, the all will start the same way)
        $filtersetL = substr($filterset->getName(), 0, 1);

        $partL = null;
        if (is_numeric($part)) {
            foreach ($this->parts as $partObj) {
                if ($partObj->getId() == $part) {
                    $partL = substr($partObj->getName(), 0, 1);
                }
            }
        } else {
            $partL = substr($part->getName(), 0, 1);
        }

        // Filter letter (manual pairing or automatic acronym)
        $filterL = '';

        $filterNameSplit = explode(' ', $filterName);
        foreach ($filterNameSplit as $word) {
            $filterL .= substr($word, 0, 1);
        }

        return strtoupper($filtersetL . $partL . $filterL);
    }

}
