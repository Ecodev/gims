<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;
use Application\Utility;

class NoteController extends AbstractRestfulController
{

    public function getList()
    {
        $surveys = Utility::explodeIds($this->params()->fromQuery('survey'));
        $questionnaires = Utility::explodeIds($this->params()->fromQuery('questionnaire'));
        $questions = Utility::explodeIds($this->params()->fromQuery('question'));

        $notes = $this->getEntityManager()->getRepository('\Application\Model\Note')->getAllByParent($surveys, $questionnaires, $questions);
        $jsonData = $this->paginate($notes);

        return new JsonModel($jsonData);
    }

}
