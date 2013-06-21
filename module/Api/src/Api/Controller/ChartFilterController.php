<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class ChartFilterController extends \Application\Controller\AbstractAngularActionController
{

    public function indexAction()
    {
        $result = array();
        $questionnaireId = $this->params()->fromQuery('questionnaire');

        // make sure the questionnaire exists
        /** @var \Application\Repository\QuestionnaireRepository $questionnaireRepository */
        $questionnaireRepository = $this->getEntityManager()->getRepository('Application\Model\Questionnaire');
        $questionnaire = $questionnaireRepository->findOneById($questionnaireId);

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

        if ($questionnaire) {

            // Fetch part
            $part = null;
            if ($partId > 0) {
                $part = $this->getEntityManager()->getRepository('Application\Model\Part')->findOneById($partId);
            }
            $parts = array();
            foreach (array($part) as $_part) {
                $parts[] = array(
                    'part'       => $_part,
                    'population' => $this->getEntityManager()->getRepository('Application\Model\Population')
                        ->getOneByQuestionnaire($questionnaire, $_part),
                );
            }

            $result = array();
            // @todo adrien, I let you check how you would like to implement this. For now I put the method "computeWithChildren" as public
            $tableController = new TableController();
            foreach ($filters as $filter) {
                $result = array_merge($result, $tableController->computeWithChildren($questionnaire, $filter, $parts));
            }
        } else {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        return new JsonModel($result);
    }
}
