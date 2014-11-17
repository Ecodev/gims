<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;
use Application\Model\Rule\FilterQuestionnaireUsage;
use Application\Model\Rule\Rule;
use Application\Utility;

class FilterController extends AbstractChildRestfulController
{

    use \Application\Traits\FlatHierarchic;

    /**
     * @return JsonModel
     */
    public function getList()
    {
        $allIndexedFilters = Utility::indexById($this->getRepository()->getAllWithPermission('read', $this->params()->fromQuery('q')));
        $flattenArray = $this->params()->fromQuery('flatten') == 'true' ? true : false;

        $parent = $this->getParent();

        if (!$parent) {
            $filters = $this->getRepository()->getRootFilters();
        } elseif ($parent instanceof \Application\Model\FilterSet) {
            $filters = $parent->getFilters();

        } elseif ($parent instanceof \Application\Model\Filter) {
            $filters = $parent->getChildren();
        }

        $items = $this->getChildren($filters, $allIndexedFilters, $flattenArray);

        $jsonData = array(
            'metadata' => array(
                'page' => 1,
                'perPage' => count($items),
                'totalCount' => count($items),
            ),
            'items' => $items,
        );

        return new JsonModel($jsonData);
    }

    /**
     * @param $filters array of filters
     * @param $allIndexedFilters
     * @param $flattenArray
     * @param int $level
     * @return array
     */
    public function getChildren($filters, $allIndexedFilters, $flattenArray, $level = 0)
    {
        $extractedFilters = [];
        $nextLevel = $level +1;
        foreach ($filters as $filter) {

            // extract
            $extractedFilter = $this->hydrator->extract($filter, $this->getJsonConfig()); // SANS children dans le GET HTTP !
            $extractedFilter['level'] = $level;

            $children = [];
            foreach ($this->getRepository()->getChildrenIds($filter->getId()) as $childId) {
                array_push($children , $allIndexedFilters[$childId]);
            }

            // recursive call for same operation on children
            $children = $this->getChildren($children, $allIndexedFilters, $flattenArray, $nextLevel);

            // if not flatten, add children to parent filter
            if ($children && !$flattenArray) {
                $extractedFilter['children'] = $children;
            }

            // add parent filter to collection
            $extractedFilters[] = $extractedFilter;

            // if flatten, add children to the same collection than parent filter
            if ($children && $flattenArray) {
                $extractedFilters = array_merge($extractedFilters, $children);
            }
        }

        return $extractedFilters;
    }

    public function getComputedFiltersAction()
    {
        $filterIds = Utility::explodeIds($this->params()->fromQuery('filters'));
        $questionnaireIds = Utility::explodeIds($this->params()->fromQuery('questionnaires'));

        $calculator = new \Application\Service\Calculator\Calculator();
        $calculator->setServiceLocator($this->getServiceLocator());
        $parts = $this->getEntityManager()->getRepository('\Application\Model\Part')->findAll();

        /* @var $cache \Application\Service\Calculator\Cache */
        $cache = $this->getServiceLocator()->get('Calculator\Cache');

        $result = array();
        foreach ($questionnaireIds as $questionnaireId) {
            $result[$questionnaireId] = array();
            foreach ($filterIds as $filterId) {
                $result[$questionnaireId][$filterId] = array();

                $key = "getComputedFiltersAction:$questionnaireId:$filterId";
                if ($cache->hasItem($key)) {
                    $valuesByPart = $cache->getItem($key);
                } else {
                    $cache->startComputing($key);
                    $valuesByPart = [];
                    foreach ($parts as $part) {
                        $value = array();
                        $value['first'] = $calculator->computeFilter($filterId, $questionnaireId, $part->getId(), false);
                        $value['second'] = $calculator->computeFilter($filterId, $questionnaireId, $part->getId(), true);
                        $valuesByPart[$part->getId()] = $value;
                    }
                    $cache->setItem($key, $valuesByPart);
                }
                $result[$questionnaireId][$filterId] = $valuesByPart;
            }
        }

        return new JsonModel($result);
    }

    public function createUsagesAction()
    {
        $filters = Utility::explodeIds($this->params()->fromQuery('filters'));
        $questionnaires = Utility::explodeIds($this->params()->fromQuery('questionnaires'));

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
