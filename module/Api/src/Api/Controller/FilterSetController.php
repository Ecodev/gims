<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class FilterSetController extends AbstractRestfulController
{

    /**
     * @param array $data
     *
     * @return mixed|void|JsonModel
     * @throws \Exception
     */
    public function create($data)
    {
        $filterSetId = empty($data['filterSetSource']) ? null : $data['filterSetSource'];

        // Retrieve a questionnaire from the storage
        $repository = $this->getEntityManager()->getRepository('Application\Model\FilterSet');

        /** @var \Application\Model\FilterSet $filterSetSource */
        $filterSetSource = $repository->findOneById($filterSetId);

        if ($filterSetSource) {

            // Special case, it should be copied from an existing.
            $modelName = $this->getModel();

            /** @var $object \Application\Model\FilterSet */
            $object = new $modelName();
            $this->hydrator->hydrate($data, $object);

            // Loops around filters
            foreach ($filterSetSource->getFilters() as $filter) {
                $object->addFilter($filter);
            }

            if (!empty($data['excludedFilters'])) {
                foreach ($data['excludedFilters'] as $excludedFilterId) {
                    // Retrieve a questionnaire from the storage
                    $_filter = $this->getEntityManager()
                        ->getRepository('Application\Model\Filter')
                        ->findOneById($excludedFilterId);
                    $object->addExcludedFilter($_filter);
                }
            }

            $this->getEntityManager()->persist($object);
            $this->getEntityManager()->flush();

            return new JsonModel($this->hydrator->extract($object, $this->getJsonConfig()));
        } else {
            $this->getResponse()->setStatusCode(404);
            $result = new JsonModel(array('message' => 'No existing filterSet found. Check parameter filterSet'));
        }
        return $result;
    }
}
