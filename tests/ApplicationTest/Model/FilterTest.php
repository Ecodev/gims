<?php

namespace ApplicationTest\Model;

class FilterTest extends AbstractModel
{

    public function testChildrenRelation()
    {
        $parent = new \Application\Model\Filter();
        $child = new \Application\Model\Filter();
        $this->assertCount(0, $parent->getChildren(), 'collection is initialized on creation');

        $parent->addChild($child);
        $this->assertCount(1, $parent->getChildren(), 'parent must be notified when child is added');
        $this->assertSame($child, $parent->getChildren()->first(), 'original child can be retreived from parent');
        $this->assertCount(1, $child->getParents(), 'child should have parent');
        $this->assertSame($parent, $child->getParents()->first(), 'original parent should be retrieved from child');

        $otherParent = new \Application\Model\Filter();
        $otherParent->addChild($child);
        $this->assertCount(1, $parent->getChildren(), 'original parent should still have child');
        $this->assertCount(1, $otherParent->getChildren(), 'new parent should have also added child');
        $this->assertSame($child, $otherParent->getChildren()->first(), 'original child can be retreived from new parent');
        $this->assertCount(2, $child->getParents(), 'child should have parent');
        $this->assertSame($parent, $child->getParents()->first(), 'original parent should be retrieved from child');
        $this->assertSame($otherParent, $child->getParents()->last(), 'original parent should be retrieved from child');
    }

}
