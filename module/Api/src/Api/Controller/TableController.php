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
            foreach($idQuestionnaires as $indexQuestionnaire => $idQuestionnaire)
            {
                $questionnaire = $questionnaireRepository->find($idQuestionnaire);
                if ($questionnaire)
                {
                    foreach($parts as $i => $p)
                    {
                        $parts[$i]['population'] = $populationRepository->getOneByQuestionnaire($questionnaire, $parts[$i]['part']);
                    }
                    foreach ($filterSet->getFilters() as $filter) {
                        $this->computeWithChildren($questionnaire, $filter, $parts, 0, $result);
                    }
                }
            }
      }

        return new JsonModel($result);
    }

    /*
     * Compute the answers for a given questionnaire (hence country+survey) and filter
     */
    public function computeWithChildren(\Application\Model\Questionnaire $questionnaire, \Application\Model\Filter $filter, array $parts, $level = 0, &$result)
    {

        $service = new \Application\Service\Calculator\Calculator();
        $service->setServiceLocator($this->getServiceLocator());
        $hydrator = new \Application\Service\Hydrator();

        $current = array();
        $current['filter'] = $hydrator->extract($filter, array('name'));
        $current['filter']['level'] = $level;

        foreach ($parts as $p) {
            $computed = $service->computeFilter($filter, $questionnaire, $p['part']);
            $current['values'][0][$p['part'] ? $p['part']->getName() : 'Total'] = $computed && $p['population']->getPopulation() ? $computed / $p['population']->getPopulation() : null;
        }

        $filterExists = false;
        foreach($result as $numRow => $data)
        {
            if ($data['filter']['id'] == $filter->getId())
            {
                $result[$numRow]['values'][] = $current['values'][0];
                $filterExists = true;
                break;
            }
        }
        if (!$filterExists)
        {
            $result[] = $current;
        }

        foreach ($filter->getChildren() as $child)
        {
            if ($child->isOfficial()) {
                $this->computeWithChildren($questionnaire, $child, $parts, $level + 1, $result);
            }
        }

    }

}
