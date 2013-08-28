<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class TableController extends \Application\Controller\AbstractAngularActionController
{

    public function indexAction()
    {
        $questionnaireParameter = $this->params()->fromQuery('questionnaire');
        $idQuestionnaires = explode(',', $questionnaireParameter);
        $questionnaireRepository = $this->getEntityManager()->getRepository('Application\Model\Questionnaire');

        $parts = array();
        foreach ($this->getEntityManager()->getRepository('Application\Model\Part')->findAll() as $part) {
            $parts[] = array(
                'part' => $part,
                'population' => null, // will be computed later for each questionnaire
            );
        }

        $populationRepository = $this->getEntityManager()->getRepository('Application\Model\Population');
        $filterSet = $this->getEntityManager()->getRepository('Application\Model\FilterSet')->findOneById($this->params()->fromQuery('filterSet'));

        $result = array();
        if ($filterSet) {
            foreach ($idQuestionnaires as $idQuestionnaire) {
                $questionnaire = $questionnaireRepository->find($idQuestionnaire);
                if ($questionnaire) {

                    // First collect parts' population
                    foreach ($parts as $i => $p) {
                        $parts[$i]['population'] = $populationRepository->getOneByQuestionnaire($questionnaire, $parts[$i]['part']);
                    }

                    // Do the actual computing for all filters
                    $resultOneQuestionnaire = array();
                    foreach ($filterSet->getFilters() as $filter) {
                        $resultOneQuestionnaire = array_merge($resultOneQuestionnaire, $this->computeWithChildren($questionnaire, $filter, $parts));
                    }

                    // Merge this questionnaire results with other questionnaire results
                    foreach ($resultOneQuestionnaire as $i => $data) {
                        if (isset($result[$i])) {
                            $result[$i]['values'][] = reset($data['values']);
                        } else {
                            $result[] = $data;
                        }
                    }
                }
            }
        }

        return new JsonModel($result);
    }

    /**
     * Comput value for the given filter and all its children recursively.
     * @param \Application\Model\Questionnaire $questionnaire
     * @param \Application\Model\Filter $filter
     * @param array $parts
     * @param integer $level the level of the current filter in the filter tree
     * @return array a list (not tree) of all filters with their values and tree level
     */
    public function computeWithChildren(\Application\Model\Questionnaire $questionnaire, \Application\Model\Filter $filter, array $parts, $level = 0)
    {
        $calculator = new \Application\Service\Calculator\Calculator();
        $calculator->setServiceLocator($this->getServiceLocator());
        $hydrator = new \Application\Service\Hydrator();

        $current = array();
        $current['filter'] = $hydrator->extract($filter, array('name'));
        $current['filter']['level'] = $level;

        foreach ($parts as $p) {
            $computed = $calculator->computeFilter($filter, $questionnaire, $p['part']);

            // Round the value
            if (!is_null($computed) && $p['population']->getPopulation()) {
                $value = round($computed / $p['population']->getPopulation(), 3);
            } else {
                $value = null;
            }

            $current['values'][0][$p['part']->getName()] = $value;
        }

        // Compute children
        $result = array($current);
        foreach ($filter->getChildren() as $child) {
            if ($child->isOfficial()) {
                $result = array_merge($result, $this->computeWithChildren($questionnaire, $child, $parts, $level + 1));
            }
        }

        return $result;
    }

}
