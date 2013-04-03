<?php

namespace ApplicationTest\Model;

class CategoryTest extends AbstractModel
{

    public function testChildrenRelation()
    {
        $parent = new \Application\Model\Category();
        $child = new \Application\Model\Category();
        $this->assertCount(0, $parent->getChildren(), 'collection is initialized on creation');

        $child->setParent($parent);
        $this->assertCount(1, $parent->getChildren(), 'parent must be notified when child is added');
        $this->assertSame($child, $parent->getChildren()->first(), 'original child can be retreived from parent');

        $otherParent = new \Application\Model\Category();
        $child->setParent($otherParent);
        $this->assertCount(0, $parent->getChildren(), 'original parent should have removed child');
        $this->assertCount(1, $otherParent->getChildren(), 'new parent should have added child');
        $this->assertSame($child, $otherParent->getChildren()->first(), 'original child can be retreived from parent');

        $child->setParent();
        $this->assertCount(0, $parent->getChildren(), 'original parent should have removed child');
        $this->assertCount(0, $otherParent->getChildren(), 'new parent should have removed child');
    }

}
