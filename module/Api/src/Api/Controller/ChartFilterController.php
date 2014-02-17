<?php

namespace Api\Controller;

use Application\View\Model\NumericJsonModel;

class ChartFilterController extends \Application\Controller\AbstractAngularActionController
{

    public function indexAction()
    {
        $questionnaire = $this->getEntityManager()->getRepository('Application\Model\Questionnaire')->findOneById($this->params()->fromQuery('questionnaire'));

        if ($questionnaire) {

            /** @var \Application\Model\Part $part */
            $part = $this->getEntityManager()->getRepository('Application\Model\Part')->findOneById($questionnaireId = $this->params()->fromQuery('part'));

            /** @var \Application\Model\Filter $filter */
            $filters = explode(',',$this->params()->fromQuery('filters'));
            $result = array();
            foreach ($filters as $filterId) {
                $filter = $this->getEntityManager()->getRepository('Application\Model\Filter')->findOneById($filterId);
                $fields = explode(',', $this->params()->fromQuery('fields'));

                // @todo adrien, I let you check how you would like to implement this. For now I put the method "computeWithChildren" of $tableController as public
                // @todo It should be some kind of service but technical leader decides...
                $tableController = new TableController();
                $tableController->setServiceLocator($this->getServiceLocator());
                $result[$filterId] = $tableController->computeWithChildren($questionnaire, $filter, array($part), 0, $fields);
            }

            return new NumericJsonModel($result);

        } else {
            $this->getResponse()->setStatusCode(404);
            return new NumericJsonModel(array('message' => 'questionnaire not found'));
        }
    }
}
