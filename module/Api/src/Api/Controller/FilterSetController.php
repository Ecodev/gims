<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;
use Application\Model\AbstractModel;

class FilterSetController extends AbstractRestfulController
{

    public function postCreate(AbstractModel $newFilterSet, array $data)
    {
        // Special case, it should be copied from an existing.
        if (!isset($data['originalFilterSet'])) {
            return;
        }

        /** @var \Application\Model\FilterSet $originalFilterSet */
        $originalFilterSet = $this->getRepository()->findOneById($data['originalFilterSet']);

        if (!$originalFilterSet) {
            $this->getEntityManager()->remove($newFilterSet);
            $this->getEntityManager()->flush();
            $this->getResponse()->setStatusCode(400);
            return new JsonModel(array('message' => 'No original filterSet found. Check parameter originalFilterSet'));
        }

        // Copy filters from original
        foreach ($originalFilterSet->getFilters() as $filter) {
            $newFilterSet->addFilter($filter);
        }

        // Add excluded filters
        if (!empty($data['excludedFilters'])) {
            $excludedFilters = $this->getEntityManager()->getRepository('Application\Model\Filter')->findById($data['excludedFilters']);
            foreach ($excludedFilters as $excludedFilter) {
                $newFilterSet->addExcludedFilter($excludedFilter);
            }
        }

        $this->getEntityManager()->flush();
    }

}
