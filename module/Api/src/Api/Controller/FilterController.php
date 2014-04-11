<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

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
                    unset($filterChildren[0]['parents']);
                    for ($i = 1; $i < $filter->getParents()->count(); $i++) {
                        unset($filterChildren[$i]);
                    }
                    $filterChildren = $this->getFlatHierarchyWithSingleRootElement($filterChildren, 'parents');
                    $filters = array_merge($filters, $filterChildren);
                }

                // else means parent is filter
            } else {
                $filters = $this->getAllChildren($parent);
                $filters = $this->flattenFilters($filters);
                $filters = $this->getFlatHierarchyWithSingleRootElement($filters, 'parents', $this->params('idParent'));
            }

            // no parent, get all filters
        } else {
            $itemOnce = $this->params()->fromQuery('itemOnce') == 'true' ? true : false;
            $filters = $this->getRepository()->findAll();
            $filters = $this->flattenFilters($filters, $itemOnce);
            $filters = $this->getFlatHierarchyWithMultipleRootElements($filters, 'parents');
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
        $jsonConfig = array_merge($this->getJsonConfig(), array('parents'));
        $flatFilters = array();
        foreach ($filters as $filter) {
            $flatFilter = $this->hydrator->extract($filter, $jsonConfig);
            if (count($flatFilter['parents']) > 0 && !$itemOnce) {
                $parents = $flatFilter['parents'];
                unset($flatFilter['parents']);
                // add multiple times the filter to list if he has multiple parents
                foreach ($parents as $parent) {
                    $filter = $flatFilter;
                    $filter['parents'] = $parent;
                    array_push($flatFilters, $filter);
                }
            } elseif (count($flatFilter['parents']) > 0 && $itemOnce) {
                $flatFilter['parents'] = $flatFilter['parents'][0];
                array_push($flatFilters, $flatFilter);
            } else {
                unset($flatFilter['parents']);
                array_push($flatFilters, $flatFilter);
            }
        }

        return $flatFilters;
    }

    private function getAllChildren($filter)
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
                    $value = $calculator->computeFilter($filterId, $questionnaireId, $part->getId());
                    $result[$questionnaireId][$filterId][$part->getId()] = $value;
                }
            }
        }

        return new JsonModel($result);
    }

}
