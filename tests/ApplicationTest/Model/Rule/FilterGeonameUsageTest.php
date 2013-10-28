<?php

namespace ApplicationTest\Model;

class FilterGeonameUsageTest extends AbstractModel
{

    public function testFilterRelation()
    {
        $geoname = new \Application\Model\Geoname();
        $usage = new \Application\Model\Rule\FilterGeonameUsage();
        $this->assertCount(0, $geoname->getFilterGeonameUsages(), 'collection is initialized on creation');

        $usage->setGeoname($geoname);
        $this->assertCount(1, $geoname->getFilterGeonameUsages(), 'filter must be notified when formula is added');
        $this->assertSame($usage, $geoname->getFilterGeonameUsages()->first(), 'original formula can be retreived from filter');
    }

}
