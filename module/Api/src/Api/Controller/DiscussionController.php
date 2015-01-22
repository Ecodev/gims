<?php

namespace Api\Controller;

use Application\Utility;
use Zend\View\Model\JsonModel;

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
