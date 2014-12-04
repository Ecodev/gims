<?php

namespace Api\Controller;

use Application\Utility;

class CommentController extends AbstractRestfulController
{

    protected function postCreate(\Application\Model\AbstractModel $comment, array $data)
    {
        Utility::executeCliCommand('email', 'notifyComment', $comment->getId());
    }

}
