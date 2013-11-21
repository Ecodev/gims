<?php

namespace Application\Repository;

use Doctrine\ORM\Query\Expr\Join;

class QuestionnaireRepository extends AbstractChildRepository
{

    /**
     * Returns all items with matching search criteria
     * @return array
     */
    public function getAllWithPermission($action = 'read', $parentName = null, \Application\Model\AbstractModel $parent = null, $search = null)
    {

        $qb = $this->createQueryBuilder('questionnaire');
        $qb->join('questionnaire.survey', 'survey', \Doctrine\ORM\Query\Expr\Join::WITH);

        if ($parent) {
            $qb->where($parentName . ' = :parent');
            $qb->setParameter('parent', $parent);
        }

        $this->addPermission($qb, 'questionnaire', \Application\Model\Permission::getPermissionName($this, $action));


        if ($search) {
            $qb->join('questionnaire.geoname', 'g', \Doctrine\ORM\Query\Expr\Join::WITH);
            $where = array();
            foreach (explode(' ', $search) as $i => $word) {
                $parameterName = 'word' . $i;
                $where[] = '(LOWER(survey.code) LIKE LOWER(:' . $parameterName . ') OR LOWER(g.name) LIKE LOWER(:' . $parameterName . '))';
                $qb->setParameter($parameterName, '%' . $word . '%');
            }
            $qb->andWhere(join(' AND ', $where));
            $qb->setMaxResults(50);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns all questionnaires for th given geoname (and load their surveys)
     * @param \Application\Model\Geoname|integer $geoname
     * @return Questionnaires[]
     */
    public function getByGeonameWithSurvey($geoname)
    {
        $qb = $this->createQueryBuilder('questionnaire');
        $qb->select('questionnaire, survey')
                ->join('questionnaire.survey', 'survey')
                ->where('questionnaire.geoname = :geoname')
                ->orderBy('questionnaire.id');

        $qb->setParameter('geoname', $geoname);

        return $qb->getQuery()->getResult();
    }

    /**
     * Returns whether the questionnaire have answers only for total part
     * @param array $questionnaires
     * @return boolean
     */
    public function isOnlyTotal(array $questionnaires)
    {
        $qb = $this->createQueryBuilder('questionnaire');
        $qb->select('COUNT(answers.id)')
                ->join('questionnaire.answers', 'answers')
                ->join('answers.part', 'part', Join::WITH, 'part.isTotal = false')
                ->where('questionnaire IN (:questionnaire)')
        ;

        $qb->setParameter('questionnaire', $questionnaires);
        $count = $qb->getQuery()->getResult(\Doctrine\ORM\AbstractQuery::HYDRATE_SINGLE_SCALAR);

        return !$count;
    }

}
