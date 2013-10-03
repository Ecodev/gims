<?php

namespace Application\Repository;

use Application\Model\Questionnaire;
use Application\Model\Filter;
use Application\Model\Part;

class AnswerRepository extends AbstractChildRepository
{

    private $cache = array();

    /**
     * Returns the percent value of an answer if it exists.
     * Optimized for mass querying wihtin a Questionnaire based on a cache.
     * @param \Application\Model\Questionnaire $questionnaire
     * @param \Application\Model\Filter $filter
     * @param \Application\Model\Part $part
     * @return float|null
     */
    public function getValuePercent(Questionnaire $questionnaire, Filter $filter, Part $part)
    {
        // If no cache for questionnaire, fill the cache
        if (!isset($this->cache[$questionnaire->getId()])) {
            $qb = $this->createQueryBuilder('answer')
                    ->select('answer.valuePercent, part.id AS part_id, filter.id AS filter_id, officialFilter.id AS officialFilter_id')
                    ->join('answer.question', 'question')
                    ->join('answer.part', 'part')
                    ->join('question.filter', 'filter')
                    ->leftJoin('filter.officialFilter', 'officialFilter')
                    ->andWhere('answer.questionnaire = :questionnaire')
            ;

            $qb->setParameters(array(
                'questionnaire' => $questionnaire->getId(),
            ));

            $res = $qb->getQuery()->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_SCALAR);

            // Restructure cache to be [questionnaireId => [filterId => [partId => value]]]
            foreach ($res as $data) {
                if ($data['officialFilter_id'])
                    $this->cache[$questionnaire->getId()][$data['officialFilter_id']][$data['part_id']] = (float) $data['valuePercent'];
                else
                    $this->cache[$questionnaire->getId()][$data['filter_id']][$data['part_id']] = (float) $data['valuePercent'];
            }
        }

        $res = @$this->cache[$questionnaire->getId()][$filter->getId()][$part->getId()];

        return $res;
    }

    /**
     * Returns all items with read access
     * @return array
     */
    public function getAllWithPermission($action = 'read', $parentName = null, \Application\Model\AbstractModel $parent = null)
    {
        $qb = $this->createQueryBuilder('answer')
                ->join('answer.questionnaire', 'questionnaire', \Doctrine\ORM\Query\Expr\Join::WITH)
                ->where($parentName . ' = :parent')
                ->setParameter('parent', $parent)
        ;

        $this->addPermission($qb, 'questionnaire', \Application\Model\Permission::getPermissionName($this, $action));

        return $qb->getQuery()->getResult();
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
                    AND question.is_population = true', $clause
        );

        return $this->getEntityManager()->getConnection()->executeUpdate($sql);
    }

}
