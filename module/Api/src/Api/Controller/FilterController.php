<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;
use Application\Model\Rule\FilterQuestionnaireUsage;
use Application\Model\Rule\Rule;

class FilterController extends AbstractChildRestfulController
{

    use \Application\Traits\FlatHierarchic;

    /**
     * @return mixed|JsonModel
     */
    public function getList()
    {
        $jsonData = $this->paginate($this->getFlatList(), false);

        return new JsonModel($jsonData);
    }

    /**
     * Return all children recursively.
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    protected function getFlatList()
    {
        $parent = $this->getParent();
        if ($parent) {

            // if parent is FilterSet, get his filters
            if ($parent instanceof \Application\Model\FilterSet) {

                $filterSetFilters = $parent->getFilters();
                $filters = array();
                foreach ($filterSetFilters as $filter) {
                    $filterChildren = array($filter);
                    $filterChildren = array_merge($filterChildren, $this->getAllChildren($filter));
                    $filterChildren = $this->flattenFilters($filterChildren);
                    unset($filterChildren[0]['_parent']);
                    for ($i = 1; $i < $filter->getParents()->count(); $i++) {
                        unset($filterChildren[$i]);
                    }
                    $filterChildren = $this->getFlatHierarchyWithSingleRootElement($filterChildren, '_parent');
                    $filters = array_merge($filters, $filterChildren);
                }

                // else means parent is filter
            } else {
                $filters = $this->getAllChildren($parent);
                $filters = $this->flattenFilters($filters);
                $filters = $this->getFlatHierarchyWithSingleRootElement($filters, '_parent', $this->params('idParent'));
            }

            // no parent, get all filters
        } else {
            $itemOnce = $this->params()->fromQuery('itemOnce') == 'true' ? true : false;
            $filters = $this->getRepository()->getAllWithPermission($this->params()->fromQuery('permission', 'read'), $this->params()->fromQuery('q'));
            $filters = $this->flattenFilters($filters, $itemOnce);
            $filters = $this->getFlatHierarchyWithMultipleRootElements($filters, '_parent');
        }

        return $filters;
    }

    /**
     * This function return a list of filters in assoc array
     * If a filter has multiple parents, he's added multiple times in the right hierarchic position
     * @param $filters
     * @param bool $itemOnce avoid to add multiple times the same filter if he has multiple parents
     * @return array
     */
    private function flattenFilters($filters, $itemOnce = false)
    {
        $jsonConfig = $this->getJsonConfig();
        $flatFilters = array();
        foreach ($filters as $filter) {
            $flatFilter = $this->hydrator->extract($filter, $jsonConfig);
            $parents = $this->hydrator->extractArray($filter->getParents(), ['id']);

            if ($parents) {
                foreach ($parents as $parent) {
                    $flatFilter['_parent'] = $parent;
                    $flatFilters[] = $flatFilter;

                    // If we don't want duplicated items for each parents, break the loop
                    if ($itemOnce) {
                        break;
                    }
                }
            } else {
                $flatFilters[] = $flatFilter;
            }
        }

        return $flatFilters;
    }

    private function getAllChildren(\Application\Model\Filter $filter)
    {
        $children = $filter->getChildren()->toArray();
        foreach ($children as $child) {
            $children = array_merge($children, $this->getAllChildren($child));
        }

        return $children;
    }

    public function getAutoCompleteListAction()
    {
        $filters = $this->getFlatList();
        $indexedFilters = array();
        foreach ($filters as &$filter) {
            $indexedFilters[$filter['id']] = $filter;
            $filter['name'] = $this->getParentsName($filter, $indexedFilters);
        }

        return new JsonModel($filters);
    }

    protected function getParentsName($filter, $index)
    {
        if (isset($filter['parents'], $index[$filter['parents']['id']])) {
            $parent = $index[$filter['parents']['id']];
            $parentsName = $this->getParentsName($parent, $index);
            $filter['name'] = $parentsName . ' / ' . $filter['name'];
        }

        return $filter['name'];
    }

    public function getComputedFiltersAction()
    {
        $filterIds = explode(',', trim($this->params()->fromQuery('filters'), ','));
        $questionnaireIds = explode(',', trim($this->params()->fromQuery('questionnaires'), ','));

        $calculator = new \Application\Service\Calculator\Calculator();
        $calculator->setServiceLocator($this->getServiceLocator());
        $parts = $this->getEntityManager()->getRepository('\Application\Model\Part')->findAll();

        $result = array();
        foreach ($questionnaireIds as $questionnaireId) {
            $result[$questionnaireId] = array();
            foreach ($filterIds as $filterId) {
                $result[$questionnaireId][$filterId] = array();
                foreach ($parts as $part) {
                    $value = array();
                    $value['first'] = $calculator->computeFilter($filterId, $questionnaireId, $part->getId(), false);
                    $value['second'] = $calculator->computeFilter($filterId, $questionnaireId, $part->getId(), true);
                    $result[$questionnaireId][$filterId][$part->getId()] = $value;
                }
            }
        }

        return new JsonModel($result);
    }

    public function getComputedWorldAction()
    {
        $calculator = new \Application\Service\Calculator\Calculator();
        $calculator->setServiceLocator($this->getServiceLocator());

        $filterIds = explode(',', trim($this->params()->fromQuery('filters'), ','));

        $filters = [];
        foreach ($filterIds as $filterId) {
            array_push($filters, $this->getRepository()->findOneById($filterId));
        }

        $geonames = $this->getEntityManager()->getRepository('\Application\Model\Geoname')->findAll();
        $parts = $this->getEntityManager()->getRepository('\Application\Model\Part')->findAll();

        /** @var \Application\Model\Geoname $geoname */
        $data = [];
        foreach ($geonames as $geoname) {
            $questionnaires = $geoname->getQuestionnaires()->toArray();
            $geonameData = $this->hydrator->extract($geoname, [
                'gtopo30',
                'population'
            ]);
            $geonameData['iso_numeric'] = $geoname->getCountry()->getIsoNumeric();
            $geonameData['iso3'] = $geoname->getCountry()->getIso3();

            foreach ($parts as $part) {
                $geonameData['calculations'][$part->getId()] = $calculator->computeFlattenAllYears(1980, 2014, $filters, $questionnaires, $part);
            }
            array_push($data, $geonameData);
        }

        return new JsonModel($data);
    }

    public function createUsagesAction()
    {
        $filters = explode(',', $this->params()->fromQuery('filters'));
        $questionnaires = explode(',', $this->params()->fromQuery('questionnaires'));

        $parts = $this->getEntityManager()->getRepository('\Application\Model\Part')->findAll();
        $filterRepo = $this->getEntityManager()->getRepository('\Application\Model\Filter');

        foreach ($filters as $filter) {
            list($parentFilter, $children) = explode(':', $filter);
            $children = explode('-', $children);
            $parent = $filterRepo->findOneById($parentFilter);
            $child1 = $filterRepo->findOneById($children[0]);
            $child2 = $filterRepo->findOneById($children[1]);

            foreach ($questionnaires as $questionnaire) {
                $questionnaire = $this->getEntityManager()->getRepository('\Application\Model\Questionnaire')->findOneById($questionnaire);

                foreach ($parts as $part) {
                    $child1Form = '{F#' . $child1->getId() . ',Q#' . $questionnaire->getId() . ',P#' . $part->getId() . '}';
                    $child2Form = '{F#' . $child2->getId() . ',Q#' . $questionnaire->getId() . ',P#' . $part->getId() . '}';
                    $questionnaireForm = '{Q#' . $questionnaire->getId() . ',P#' . $part->getId() . '}';
                    $completeForm = '=(' . $child1Form . '*' . $child2Form . '/' . $questionnaireForm . ')';

                    $rule = new Rule('Sector for "' . $parent->getName() + '"');
                    $rule->setFormula($completeForm);

                    $fqu = new FilterQuestionnaireUsage();
                    $fqu->setQuestionnaire($questionnaire);
                    $fqu->setFilter($parent);
                    $fqu->setRule($rule);
                    $fqu->setPart($part);
                    $fqu->setJustification('Sector for "' . $parent->getName() + '"');

                    $this->getEntityManager()->persist($rule);
                    $this->getEntityManager()->persist($fqu);
                }
            }
        }
        $this->getEntityManager()->flush();

        return new JsonModel(array());
    }

    public function getSectorFiltersForGeonameAction()
    {
        $filter = $this->getEntityManager()->getRepository('\Application\Model\Geoname')->getSectorFilter($this->params()->fromQuery('geoname'));

        return new JsonModel($filter);
    }

}
