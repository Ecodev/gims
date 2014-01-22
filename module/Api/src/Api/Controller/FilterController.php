<?php

namespace Api\Controller;

use Application\Model\Filter;
use Application\Service\Hydrator;
use Zend\View\Model\JsonModel;

class FilterController extends AbstractRestfulController
{

    /**
     * @return mixed|JsonModel
     */
    public function getList()
    {
        $filters = $this->getRepository()->getOfficialRoots();

        return new JsonModel($this->hydrator->extractArray($filters, $this->getJsonConfig()));
    }

}
