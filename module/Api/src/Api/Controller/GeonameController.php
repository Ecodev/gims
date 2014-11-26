<?php

namespace Api\Controller;

use Zend\View\Model\JsonModel;

class GeonameController extends AbstractRestfulController
{

    /**
     *
     */
    public function getPopulationAction()
    {
        $geoname = $this->getRepository('\Application\Model\Geoname')->findOneById($this->params()->fromQuery('geoname'));

        $populationRepository = $this->getEntityManager()->getRepository('Application\Model\Population');
        $populations = $populationRepository->getAllYearsForGeonameByPart($geoname);

        return new JsonModel($populations);
    }

}
