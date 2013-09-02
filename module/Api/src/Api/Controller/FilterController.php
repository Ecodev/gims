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
    protected function getClosures()
    {
        $config = array(
            'children' => $closure = function (Hydrator $hydrator, Filter $filter) {
                $result = array();
                foreach ($filter->getChildren() as $child) {
                    if ($child->isOfficial()) {
                        $result[] = $hydrator->extract($child, Filter::getJsonConfig());
                    }
                }

                return $result;
            }
        );

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
