<?php

namespace ApplicationTest\Model;

class FilterFormulaTest extends AbstractModel
{

    public function testFilterRelation()
    {
        $filter = new \Application\Model\Filter();
        $relation = new \Application\Model\Rule\FilterFormula();
        $this->assertCount(0, $filter->getFilterFormulas(), 'collection is initialized on creation');

        $relation->setFilter($filter);
        $this->assertCount(1, $filter->getFilterFormulas(), 'filter must be notified when formula is added');
        $this->assertSame($relation, $filter->getFilterFormulas()->first(), 'original formula can be retreived from filter');
    }

}
