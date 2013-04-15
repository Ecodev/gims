<?php

namespace Application\Repository;

class PopulationRepository extends AbstractRepository
{

    /**
     * Returns the population for given questionnaire and part
     * @param \Application\Model\Questionnaire $questionnaire
     * @param \Application\Model\Part $part
     * @return \Application\Model\Population
     */
    public function getOneByQuestionnaire(\Application\Model\Questionnaire $questionnaire, \Application\Model\Part $part = null)
    {
        $country = $this->getEntityManager()->getRepository('Application\Model\Country')->findOneByGeoname($questionnaire->getGeoname());

        return $this->findOneBy(array(
                    'part' => $part,
                    'year' => $questionnaire->getSurvey()->getYear(),
                    'country' => $country,
        ));
    }

}
