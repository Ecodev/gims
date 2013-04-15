<?php

namespace Application\Repository;

class AnswerRepository extends AbstractRepository
{

    /**
     * Compute absolute value from percentage value, based on population (for JMP)
     * @return integer row modifed count
     */
    public function updateAbsoluteValueFromPercentageValue()
    {
        return $this->getEntityManager()->getConnection()->executeUpdate('
            UPDATE answer SET value_absolute = p.population * value_percent
            FROM questionnaire q
                JOIN survey s ON (q.survey_id = s.id)
                JOIN geoname g ON (q.geoname_id = g.id)
                JOIN country c ON (c.geoname_id = g.id)
                JOIN population p ON (p.country_id = c.id AND s.year = p.year)
            WHERE answer.value_absolute IS NULL
                AND answer.questionnaire_id = q.id
                AND (answer.part_id = p.part_id OR answer.part_id IS NULL AND p.part_id IS NULL)
            ');
    }

}
