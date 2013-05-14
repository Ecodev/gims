<?php

namespace Api\Controller;

use Application\Model\Filter;
use Application\Service\Hydrator;
use Zend\View\Model\JsonModel;

class FilterController extends AbstractRestfulController
{

    /**
     * @return array
     */
    protected function getJsonConfig()
    {
        $config = array(
            'name',
            'isOfficial',
            'summands' => array(
                'name',
            ),
        );

        $closure = function (Hydrator $hydrator, Filter $filter) use ($config) {
            $result = array();
            foreach ($filter->getChildren() as $child) {
                if ($child->isOfficial()) {
                    $result[] = $hydrator->extract($child, $config);
                }
            }
            return $result;
        };

        $config['children'] = $closure;
        return $config;
    }

    /**
     * @return mixed|JsonModel
     */
    public function getList()
    {
        $filters = $this->getRepository()->getOfficialRoots();

        return new JsonModel($this->hydrator->extractArray($filters, $this->getJsonConfig()));
    }

}
