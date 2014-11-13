<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;
use Application\Utility;

class DiscussionController extends AbstractRestfulController
{

    public function getList()
    {
        $surveys = Utility::explodeIds($this->params()->fromQuery('surveys'));
        $questionnaires = Utility::explodeIds($this->params()->fromQuery('questionnaires'));
        $filters = Utility::explodeIds($this->params()->fromQuery('filters'));

        $comments = $this->getEntityManager()->getRepository('\Application\Model\Discussion')->getAllByParent($surveys, $questionnaires, $filters, $this->params()->fromQuery('q'));
        $jsonData = $this->paginate($comments);

        return new JsonModel($jsonData);
    }

}
