<?php

namespace ApplicationTest\Model;

use Application\Model\Comment;
use Application\Model\Discussion;

/**
 * @group Model
 */
class DiscussionTest extends AbstractModel
{

    public function testCommentsRelation()
    {
        $discussion = new Discussion();
        $comment = new Comment();

        $this->assertCount(0, $discussion->getComments(), 'collection is initialized on creation');

        $comment->setDiscussion($discussion);
        $this->assertCount(1, $discussion->getComments(), 'discussion must be notified when comment is added');
        $this->assertSame($comment, $discussion->getComments()->first(), 'original comment can be retrieved from discussion');
    }

}
