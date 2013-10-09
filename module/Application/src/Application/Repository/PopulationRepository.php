<?php

namespace Application\Repository;

class PopulationRepository extends AbstractRepository
{

    /**
     * Returns the population for given questionnaire and part
     * @param \Application\Model\Questionnaire $questionnaire
     * @param integer $partId
     * @return \Application\Model\Population
     */
    public function getOneByQuestionnaire(\Application\Model\Questionnaire $questionnaire, $partId)
    {
        $query = $this->getEntityManager()->createQuery("SELECT p FROM Application\Model\Population p
            JOIN p.country c
            JOIN c.geoname g
            JOIN Application\Model\Questionnaire q
            WHERE
            q.geoname = g
            AND q = :questionnaire
            AND p.year = :year
            AND p.part = :part"
        );

        $params = array(
            'questionnaire' => $questionnaire,
            'year' => $questionnaire->getSurvey()->getYear(),
            'part' => $partId,
        );

        $query->setParameters($params);
        $population = $query->getOneOrNullResult();

        return $population;
    }

    /**
     * Returns the population for given geoname, part and year
     * @param \Application\Model\Geoname $geoname
     * @param \Application\Model\Part $part
     * @return \Application\Model\Population
     */
    public function getOneByGeoname(\Application\Model\Geoname $geoname, \Application\Model\Part $part, $year)
    {
        $query = $this->getEntityManager()->createQuery("SELECT p FROM Application\Model\Population p
            JOIN p.country country
            WHERE
            country.geoname = :geoname
            AND p.year = :year
            AND p.part = :part"
        );

        $params = array(
            'geoname' => $geoname,
            'year' => $year,
            'part' => $part,
        );

        $query->setParameters($params);
        $population = $query->getOneOrNullResult();

        return $population;
    }

}
