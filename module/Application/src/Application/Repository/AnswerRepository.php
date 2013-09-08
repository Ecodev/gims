<?php

namespace Application\Repository;

class AnswerRepository extends AbstractRepository
{

    /**
     * Returns all items with read access
     * @return array
     */
    public function getAllWithPermission($parentName, \Application\Model\AbstractModel $parent = null)
    {
        $permissionDql = $this->getPermissionDql('questionnaire', 'Answer-read');
        $query = $this->getEntityManager()->createQuery("SELECT answer
            FROM Application\Model\Answer answer
            JOIN answer.questionnaire questionnaire
            $permissionDql
            WHERE
            $parentName = :parent
            "
        );

        $query->setParameters(array(
            'parent' => $parent
        ));

        return $query->getResult();
    }

    /**
     * Compute absolute value from percentage value, based on population (for JMP)
     *
     * @param \Application\Model\Answer $answer optional answer to limit on what we compute thing
     * @return integer row modifed count
     */
    public function updateAbsoluteValueFromPercentageValue(\Application\Model\Answer $answer = NULL)
    {
        // if we have an answer we could limit the scope of the request
        $clause = $answer ? 'answer.id = ' . $answer->getId() : 'answer.value_absolute IS NULL';
        $sql = sprintf(
                'UPDATE answer SET value_absolute = p.population * value_percent
                FROM questionnaire q
                    JOIN survey s ON (q.survey_id = s.id)
                    JOIN geoname g ON (q.geoname_id = g.id)
                    JOIN country c ON (c.geoname_id = g.id)
                    JOIN population p ON (p.country_id = c.id AND s.year = p.year)
                    JOIN question ON (s.id = question.survey_id)
                WHERE %s
                    AND answer.part_id = p.part_id
                    AND answer.questionnaire_id = q.id
                    AND answer.question_id = question.id
                    AND question.is_population = true',
            $clause

        );

        return $this->getEntityManager()->getConnection()->executeUpdate($sql);
    }

}
