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

    public function aAction()
    {
        $questionnaires = $this->getRepository()->findBy(array('id' => 167));
        $questionnaires = $this->getRepository()->findAll();
        $filter = $this->getEntityManager()->getRepository('Application\Model\Filter')->findOneBy(array('name' => $this->params()->fromQuery('filter', 'Water')));
        $part = $this->getEntityManager()->getRepository('Application\Model\Part')->findOneBy(array('name' => $this->params()->fromQuery('part')));

        $calculator = new \Application\Service\Calculator\Jmp();
        $calculator->setServiceLocator($this->getServiceLocator());

        switch ($this->params()->fromQuery('c')) {
            case 'flatten':
                $result = $calculator->computeFlatten(1980, 2011, $questionnaires, $filter, $part);
                break;
            case 'regression':
                $result = array();
                foreach (range(1980, 2011) as $year) {
                    $result[$year] = $calculator->computeRegression($year, $questionnaires, $filter, $part);
                }
                break;
            default:
                $result = $calculator->computeFilter($questionnaires, $filter->getCategoryFilterComponents()->get(1), $part);
        }

        return new JsonModel($result);
    }

}
