<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class NoteController extends AbstractRestfulController
{

    public function getList()
    {

        $surveys = $this->params()->fromQuery('survey') ? explode(',', $this->params()->fromQuery('survey')) : null;
        $questionnaires = $this->params()->fromQuery('questionnaire') ? explode(',', $this->params()->fromQuery('questionnaire')) : null;
        $questions = $this->params()->fromQuery('question') ? explode(',', $this->params()->fromQuery('question')) : null;

        $notes = $this->getEntityManager()->getRepository('\Application\Model\Note')->getAllByParent($surveys, $questionnaires, $questions);
        $jsonData = $this->paginate($notes);

        return new JsonModel($jsonData);
    }

}
