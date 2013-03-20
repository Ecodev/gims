<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class AnswerController extends AbstractRestfulController
{

    protected function getJsonConfig()
    {
        return array(
            'valuePercent',
            'valueAbsolute',
            'question' => array(
                'name',
                'category' => array(
                    'name'
                ),
            )
        );
    }

    public function getList()
    {
        $idQuestionnaire = $this->params('idQuestionnaire');
        $c = array(
            'questionnaire' => $idQuestionnaire,
        );

        $objects = $this->getRepository()->findBy($c);

        return new JsonModel($this->arrayOfObjectsToArray($objects, $this->getJsonConfig()));
    }
}
