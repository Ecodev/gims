<?php

namespace ApplicationTest\Model;

class FilterUsageTest extends AbstractModel
{

    public function testFilterRelation()
    {
        $filter = new \Application\Model\Filter();
        $usage = new \Application\Model\Rule\FilterUsage();
        $this->assertCount(0, $filter->getFilterUsages(), 'collection is initialized on creation');

        $usage->setFilter($filter);
        $this->assertCount(1, $filter->getFilterUsages(), 'filter must be notified when formula is added');
        $this->assertSame($usage, $filter->getFilterUsages()->first(), 'original formula can be retreived from filter');
    }

}
