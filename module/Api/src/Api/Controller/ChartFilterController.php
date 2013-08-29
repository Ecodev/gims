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

        // fetch part
        $partId = $questionnaireId = $this->params()->fromQuery('part');

        // fetch filter set
        $filterSetId = $questionnaireId = $this->params()->fromQuery('filterSet');

        /** @var \Application\Model\FilterSet $filterSet */
        $filterSet = $this->getEntityManager()->getRepository('Application\Model\FilterSet')->findOneById($filterSetId);
        $excludedFilters = array();
        foreach ($filterSet->getExcludedFilters() as $excludedFilter) {
            $excludedFilters[] = $excludedFilter->getId();
        }

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
            $part = $this->getEntityManager()->getRepository('Application\Model\Part')->findOneById($partId);
            $parts = array();
            foreach (array($part) as $_part) {
                $parts[] = array(
                    'part'       => $_part,
                    'population' => $this->getEntityManager()->getRepository('Application\Model\Population')
                        ->getOneByQuestionnaire($questionnaire, $_part),
                );
            }

            // @todo adrien, I let you check how you would like to implement this. For now I put the method "computeWithChildren" of $tableController as public
            // @todo It should be some kind of service but technical leader decides...
            $tableController = new TableController();
            $tableController->setServiceLocator($this->getServiceLocator());
            $result = array();
            foreach ($filters as $filter) {
                $result = array_merge($result, $tableController->computeWithChildren($questionnaire, $filter, $parts));
            }

            // Add information whether the filter is selectable or not
            $resultNumber = count($result);
            $partName = $parts[0]['part']->getName();
            for ($index = 0; $index < $resultNumber; $index++) {
                $currentResult = &$result[$index];
                $nextResult = null;
                if (isset($result[$index + 1])) {
                    $nextResult = $result[$index + 1];
                }

                $currentResult['selected'] = false;
                // algorithm computing whether the raw is selectable
                $filterValue = $currentResult['values'][0][$partName];
                if ($filterValue === null) {
                    $currentResult['selectable'] = false;
                } elseif ($currentResult['filter']['level'] < $nextResult['filter']['level']
                    && $nextResult !== null)
                {
                    $currentResult['selectable'] = false;
                } else {
                    $currentResult['selectable'] = true;
                    $currentResult['selected'] = true;
                    if (in_array($currentResult['filter']['id'], $excludedFilters)) {
                        $currentResult['selected'] = false;
                    }
                }
            }

        } else {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        return new NumericJsonModel($result);
    }
}
