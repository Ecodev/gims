<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class FilterSetController extends AbstractRestfulController
{

    /**
     * @param array    $data
     *
     * @param callable $postAction
     *
     * @return mixed|void|JsonModel
     */
    public function create($data, \Closure $postAction = null)
    {
        $filterSetId = empty($data['originalFilterSet']) ? null : $data['originalFilterSet'];

        // Retrieve a questionnaire from the storage
        $repository = $this->getEntityManager()->getRepository('Application\Model\FilterSet');

        /** @var \Application\Model\FilterSet $originalFilterSet */
        $originalFilterSet = $repository->findOneById($filterSetId);

        if ($originalFilterSet) {

            // Special case, it should be copied from an existing.
            $modelName = $this->getModel();

            /** @var $object \Application\Model\FilterSet */
            $newFilterSet = new $modelName();
            $this->hydrator->hydrate($data, $newFilterSet);

            // Loops around filters
            foreach ($originalFilterSet->getFilters() as $filter) {
                $newFilterSet->addFilter($filter);
            }

            if (!empty($data['excludedFilters'])) {
                foreach ($data['excludedFilters'] as $excludedFilterId) {
                    // Retrieve a questionnaire from the storage
                    $_filter = $this->getEntityManager()
                        ->getRepository('Application\Model\Filter')
                        ->findOneById($excludedFilterId);
                    $newFilterSet->addExcludedFilter($_filter);
                }
            }

            $this->getEntityManager()->persist($newFilterSet);
            $this->getEntityManager()->flush();

            return new JsonModel($this->hydrator->extract($newFilterSet, $this->getJsonConfig()));
        } else {
            $this->getResponse()->setStatusCode(404);
            $result = new JsonModel(array('message' => 'No existing filterSet found. Check parameter filterSet'));
        }

        return $result;
    }
}
