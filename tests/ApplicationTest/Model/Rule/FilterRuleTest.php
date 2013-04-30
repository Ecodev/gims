<?php

namespace ApplicationTest\Model;

class FilterRuleTest extends AbstractModel
{

    public function testFilterRelation()
    {
        $filter = new \Application\Model\Filter();
        $relation = new \Application\Model\Rule\FilterRule();
        $this->assertCount(0, $filter->getFilterRules(), 'collection is initialized on creation');

        $relation->setFilter($filter);
        $this->assertCount(1, $filter->getFilterRules(), 'filter must be notified when rule is added');
        $this->assertSame($relation, $filter->getFilterRules()->first(), 'original rule can be retreived from filter');
    }

}
