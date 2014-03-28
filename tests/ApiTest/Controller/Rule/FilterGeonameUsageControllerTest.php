<?php

namespace ApiTest\Controller\Rule;

use ApiTest\Controller\AbstractChildRestfulControllerTest;

/**
 * @group Rest
 */
class FilterGeonameUsageControllerTest extends AbstractChildRestfulControllerTest
{

    protected function getAllowedFields()
    {
        return array('id', 'rule', 'filter', 'part', 'geoname', 'justification');
    }

    protected function getTestedObject()
    {
        return $this->filterGeonameUsage;
    }

    protected function getPossibleParents()
    {
        return array(
            $this->filterGeonameUsage->getRule(),
            $this->filterGeonameUsage->getFilter(),
            $this->filterGeonameUsage->getGeoname(),
        );
    }

}
