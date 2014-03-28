<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class RuleController extends AbstractRestfulController
{

    /**
     * Override to use correct model
     * @return string
     */
    protected function getModel()
    {
        return '\Application\Model\Rule\Rule';
    }

}
