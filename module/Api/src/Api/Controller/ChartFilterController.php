<?php

namespace Api\Controller;

use Application\View\Model\NumericJsonModel;

class ChartFilterController extends \Application\Controller\AbstractAngularActionController
{

    public function indexAction()
    {
        $questionnaireId = $this->params()->fromQuery('questionnaire');

        // make sure the questionnaire exists
        /** @var \Application\Repository\QuestionnaireRepository $questionnaireRepository */
        $questionnaireRepository = $this->getEntityManager()->getRepository('Application\Model\Questionnaire');
        $questionnaire = $questionnaireRepository->findOneById($questionnaireId);

        if ($questionnaire) {

            // fetch part
            $partId = $questionnaireId = $this->params()->fromQuery('part');

            // fetch filter
            $filterId = $questionnaireId = $this->params()->fromQuery('filter');

            /** @var \Application\Repository\FilterRepository $filterRepository */
            $filterRepository = $this->getEntityManager()->getRepository('Application\Model\Filter');

            /** @var \Application\Model\Filter $filter */
            $filter = $filterRepository->findOneById($filterId);

            // Get official filter
            $filters = array();
            /** @var \Application\Model\Filter $_filter */
            foreach ($filter->getChildren() as $_filter) {
                if ($_filter->isOfficial()) {
                    $filters[] = $_filter;
                }
            }

            // Fetch part
            $part = $this->getEntityManager()->getRepository('Application\Model\Part')->findOneById($partId);

            // @todo adrien, I let you check how you would like to implement this. For now I put the method "computeWithChildren" of $tableController as public
            // @todo It should be some kind of service but technical leader decides...
            $tableController = new TableController();
            $tableController->setServiceLocator($this->getServiceLocator());
            $result = array();
            foreach ($filters as $filter) {
                $result = array_merge($result, $tableController->computeWithChildren($questionnaire, $filter,  array($part)));
            }

            return new NumericJsonModel($result);

        } else {
            $this->getResponse()->setStatusCode(404);
            return new NumericJsonModel(array('message' => 'questionnaire not found'));
        }
    }
}
