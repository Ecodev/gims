<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class TableController extends \Application\Controller\AbstractAngularActionController
{

    public function indexAction()
    {
        $questionnaireParameter = $this->params()->fromQuery('questionnaire');
        $idQuestionnaires = explode(',', $questionnaireParameter);
        $idQuestionnaire = array_shift($idQuestionnaires); // this the first parameter
        $questionnaireRepository = $this->getEntityManager()->getRepository('Application\Model\Questionnaire');


        $questionnaire = $questionnaireRepository->find($idQuestionnaire);

        $pp = $this->getEntityManager()->getRepository('Application\Model\Part')->findAll();
        array_unshift($pp, null);
        $parts = array();
        foreach ($pp as $part) {
            $parts[] = array(
                'part' => $part,
                'population' => $this->getEntityManager()->getRepository('Application\Model\Population')->getOneByQuestionnaire($questionnaire, $part),
            );
        }

        if (!$questionnaire) {
            $this->getResponse()->setStatusCode(404);
            return;
        }

        $filterSet = $this->getEntityManager()->getRepository('Application\Model\FilterSet')->findOneById($this->params()->fromQuery('filterSet'));

        $result = array();
        if ($filterSet) {
            foreach ($filterSet->getFilters() as $filter) {
                $result = array_merge($result, $this->computeWithChildren($questionnaire, $filter, $parts));
            }

            $sideResults = array();
            $_result = array();
            foreach ($idQuestionnaires as $id) {
                $sideQuestionnaire = $questionnaireRepository->find($id);
                foreach ($filterSet->getFilters() as $filter) {
                    $_result = array_merge($_result, $this->computeWithChildren($sideQuestionnaire, $filter, $parts));
                }

                // store result
                $sideResults[$id] = $_result;
            }

            // merge back data with other questionnaires
            foreach ($sideResults as $sideResult) {

                foreach ($sideResult as $key => $data) {
                    $result[$key]['values'][] = $data['values'][0];
                }
            }
        }

        return new JsonModel($result);
    }

    private function computeWithChildren(\Application\Model\Questionnaire $questionnaire, \Application\Model\Filter $filter, array $parts, $level = 0)
    {

        $service = new \Application\Service\Calculator\Calculator();
        $hydrator = new \Application\Service\Hydrator();

        $current = array();
        $current['filter'] = $hydrator->extract($filter, array('name'));
        $current['filter']['level'] = $level;

        foreach ($parts as $p) {
            $computed = $service->computeFilter($filter, $questionnaire, $p['part']);
            $current['values'][0][$p['part'] ? $p['part']->getName() : 'Total'] = $computed && $p['population']->getPopulation() ? $computed / $p['population']->getPopulation() : null;
        }

        $result = array($current);
        foreach ($filter->getChildren() as $child) {
            if ($child->isOfficial()) {
                $result = array_merge($result, $this->computeWithChildren($questionnaire, $child, $parts, $level + 1));
            }
        }

        return $result;
    }

}
