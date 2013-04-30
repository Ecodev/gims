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

        $query = $this->getEntityManager()->createQuery("SELECT p FROM Application\Model\Population p
            JOIN p.country c
            JOIN c.geoname g
            JOIN Application\Model\Questionnaire q
            WHERE
            q.geoname = g
            AND q = :questionnaire
            AND p.year = :year
            AND (p.part " . ($part ? "= :part" : "IS NULL") . ")"
                )
        ;

        $params = array(
            'questionnaire' => $questionnaire,
            'year' => $questionnaire->getSurvey()->getYear(),
        );

        if ($part)
            $params['part'] = $part;

        $query->setParameters($params);

        $population = $query->getOneOrNullResult();

        return $population;
    }

}
