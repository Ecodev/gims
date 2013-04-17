<?php

namespace ApplicationTest\Model;

class CategoryFilterComponentRuleTest extends AbstractModel
{

    public function testCategoryFilterComponentRelation()
    {
        $categoryFilterComponent = new \Application\Model\CategoryFilterComponent();
        $relation = new \Application\Model\Rule\CategoryFilterComponentRule();
        $this->assertCount(0, $categoryFilterComponent->getCategoryFilterComponentRules(), 'collection is initialized on creation');

        $relation->setCategoryFilterComponent($categoryFilterComponent);
        $this->assertCount(1, $categoryFilterComponent->getCategoryFilterComponentRules(), 'categoryFilterComponent must be notified when rule is added');
        $this->assertSame($relation, $categoryFilterComponent->getCategoryFilterComponentRules()->first(), 'original rule can be retreived from categoryFilterComponent');
    }

}
