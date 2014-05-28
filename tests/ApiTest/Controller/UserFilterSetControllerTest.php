<?php

namespace ApiTest\Controller;

/**
 * @group Rest
 */
class UserFilterSetControllerTest extends AbstractChildRestfulControllerTest
{

    protected function getAllowedFields()
    {
        return array('id', 'user', 'role', 'filterSet');
    }

    protected function getTestedObject()
    {
        return $this->userFilterSet;
    }

    protected function getPossibleParents()
    {
        return [
            $this->user,
            $this->filterSet,
        ];
    }

}
