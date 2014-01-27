<?php

namespace Api\Controller;

use Application\Model\Filter;
use Application\Service\Hydrator;
use Zend\View\Model\JsonModel;

class FilterController extends AbstractRestfulController
{
    use \Application\Traits\FlatHierarchic;

    /**
     * @return mixed|JsonModel
     */
    public function getList()
    {
        $filters = $this->getRepository()->findAll();

        $jsonConfig = array_merge($this->getJsonConfig(), array('parents'));

        $flatFilters = array();
        foreach ($filters as $filter) {
            $flatFilter = $this->hydrator->extract($filter, $jsonConfig);
            if (count($flatFilter['parents']) > 0) {
                $parents = $flatFilter['parents'];
                unset($flatFilter['parents']);
                foreach ($parents as $parent) {
                    $filter = $flatFilter;
                    $filter['parent'] = $parent;
                    array_push($flatFilters, $filter);
                }
            } else {
                unset($flatFilter['parents']);
                array_push($flatFilters, $flatFilter);
            }
        }

        $flatFilters = $this->getFlatHierarchy($flatFilters, 'parent');

        return new JsonModel($flatFilters);
    }

}
