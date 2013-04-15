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

        $categoryRepository = $this->getEntityManager()->getRepository('Application\Model\Category');

        $topCategories = $categoryRepository->findBy(array(
            'parent' => null,
            'official' => true,
        ));

        $result = array();
        $result[] = $questionnaire->getSurvey()->getName() . ', ' . $questionnaire->getSurvey()->getCode() . ', ' . $questionnaire->getGeoname()->getName();
        foreach ($topCategories as $category) {
            $result [] = $this->computeWithChildren($questionnaire, $population, $category, $part);
        }

        return new JsonModel($result);
    }

    private function computeWithChildren(\Application\Model\Questionnaire $questionnaire, \Application\Model\Population $population, \Application\Model\Category $category, \Application\Model\Part $part = null)
    {
        $result = array();
        $computed = $questionnaire->compute($category, $part);
        $result[$category->getName()] = $computed && $population->getPopulation() ? $computed / $population->getPopulation() : null;

        $children = array();
        foreach ($category->getChildren() as $child) {
            if ($child->getOfficial()) {
                $children[] = $this->computeWithChildren($questionnaire, $population, $child, $part);
            }
        }

        if ($children)
            $result['children'] = $children;

        return $result;
    }

}
