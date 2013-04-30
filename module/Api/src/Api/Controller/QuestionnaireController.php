<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class QuestionnaireController extends AbstractRestfulController
{

    protected function getJsonConfig()
    {
        return array(
            'dateObservationStart',
            'dateObservationEnd',
            'survey' => array(
                'code',
                'name'
            ),
        );
    }

    public function computeAction()
    {
        $idQuestionnaire = $this->params('idQuestionnaire');
        $questionnaireRepository = $this->getEntityManager()->getRepository('Application\Model\Questionnaire');
        $questionnaire = $questionnaireRepository->find($idQuestionnaire);
        $part = $this->getEntityManager()->getRepository('Application\Model\Part')->findOneBy(array('name' => $this->params()->fromQuery('part')));
        $population = $this->getEntityManager()->getRepository('Application\Model\Population')->getOneByQuestionnaire($questionnaire, $part);

        if (!$questionnaire) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $filterRepository = $this->getEntityManager()->getRepository('Application\Model\Filter');

        $officialRootFilters = $filterRepository->getOfficialRoots();

        $result = array();
        $result[] = $questionnaire->getSurvey()->getName() . ', ' . $questionnaire->getSurvey()->getCode() . ', ' . $questionnaire->getGeoname()->getName();
        foreach ($officialRootFilters as $filter) {
            $result [] = $this->computeWithChildren($questionnaire, $population, $filter, $part);
        }

        return new JsonModel($result);
    }

    private function computeWithChildren(\Application\Model\Questionnaire $questionnaire, \Application\Model\Population $population, \Application\Model\Filter $filter, \Application\Model\Part $part = null)
    {

        $service = new \Application\Service\Calculator\Calculator();

        $result = array();
        $computed = $service->computeFilter($filter, $questionnaire, $part);
        $result[$filter->getName()] = $computed && $population->getPopulation() ? $computed / $population->getPopulation() : null;

        $children = array();
        foreach ($filter->getChildren() as $child) {
            if ($child->isOfficial()) {
                $children[] = $this->computeWithChildren($questionnaire, $population, $child, $part);
            }
        }

        if ($children)
            $result['children'] = $children;

        return $result;
    }

}
