<?php

namespace ApplicationTest\Model\Rule;

/**
 * @group Model
 */
class FilterGeonameUsageTest extends \ApplicationTest\Model\AbstractModel
{

    public function testRelations()
    {
        $usage = new \Application\Model\Rule\FilterGeonameUsage();

        // Test Rule
        $rule = new \Application\Model\Rule\Rule();
        $this->assertCount(0, $rule->getFilterGeonameUsages(), 'collection is initialized on creation');
        $usage->setRule($rule);
        $this->assertCount(1, $rule->getFilterGeonameUsages(), 'rule must be notified when usage is added');
        $this->assertSame($usage, $rule->getFilterGeonameUsages()->first(), 'original usage can be retrieved from rule');

        // Test Geoname
        $geoname = new \Application\Model\Geoname();
        $this->assertCount(0, $geoname->getFilterGeonameUsages(), 'collection is initialized on creation');
        $usage->setGeoname($geoname);
        $this->assertCount(1, $geoname->getFilterGeonameUsages(), 'geoname must be notified when usage is added');
        $this->assertSame($usage, $geoname->getFilterGeonameUsages()->first(), 'original usage can be retrieved from geoname');
    }
}
