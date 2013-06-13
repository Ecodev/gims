<?php

namespace Application\Repository;

class QuestionnaireRepository extends AbstractRepository
{
    /**
     * Returns an array of questionnaire matching search criteria
     * @param \Application\Model\Survey $survey
     * @param string $search
     * @return array
     */
    public function getAll(\Application\Model\Survey $survey = null, $search = null)
    {
        $qb = $this->createQueryBuilder('q');
        $parameter = 0;
        if ($survey || $search) {
            $qb->join('q.survey', 's', \Doctrine\ORM\Query\Expr\Join::WITH);

            if ($survey) {
                $qb->andWhere('s = ?' . $parameter);
                $qb->setParameter($parameter++, $survey);
            }
        }

        if ($search) {
            $qb->join('q.geoname', 'g', \Doctrine\ORM\Query\Expr\Join::WITH);
            $where = array();
            foreach (explode(' ', $search) as $word) {
                $where[] = '(LOWER(s.code) LIKE LOWER(?' . $parameter . ') OR LOWER(g.name) LIKE LOWER(?' . $parameter . '))';
                $qb->setParameter($parameter++, '%' . $word . '%');
            }
            $qb->andWhere(join(' AND ', $where));
            $qb->setMaxResults(50);
        }

        $result = $qb->getQuery()->getResult();

        return $result;
    }

}
