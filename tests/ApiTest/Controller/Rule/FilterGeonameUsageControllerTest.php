<?php

namespace ApiTest\Controller\Rule;

/**
 * @group Rest
 */
class FilterGeonameUsageControllerTest extends AbstractUsageControllerTest
{

    protected function getAllowedFields()
    {
        return ['id', 'rule', 'filter', 'part', 'geoname', 'justification'];
    }

    protected function getTestedObject()
    {
        return $this->filterGeonameUsage;
    }

    protected function getPossibleParents()
    {
        return [
            $this->filterGeonameUsage->getRule(),
            $this->filterGeonameUsage->getFilter(),
            $this->filterGeonameUsage->getGeoname(),
        ];
    }

}
