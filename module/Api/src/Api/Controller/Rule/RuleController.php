<?php

namespace Api\Controller\Rule;

use Api\Controller\AbstractRestfulController;

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
