<?php

namespace ApplicationTest\Model;

class FilterQuestionnaireUsageTest extends AbstractModel
{

    public function testFilterRelation()
    {
        $filter = new \Application\Model\Filter();
        $usage = new \Application\Model\Rule\FilterQuestionnaireUsage();
        $this->assertCount(0, $filter->getFilterQuestionnaireUsages(), 'collection is initialized on creation');

        $usage->setFilter($filter);
        $this->assertCount(1, $filter->getFilterQuestionnaireUsages(), 'filter must be notified when rule is added');
        $this->assertSame($usage, $filter->getFilterQuestionnaireUsages()->first(), 'original rule can be retreived from filter');
    }

}
