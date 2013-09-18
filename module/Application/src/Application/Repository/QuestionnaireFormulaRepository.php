<?php

namespace Application\Repository;

class QuestionnaireFormulaRepository extends AbstractRepository
{

    public function getAllByFormulaName(array $formulaNames, array $questionnaires, \Application\Model\Part $part)
    {
        $qb = $this->createQueryBuilder('qf');
        $qb->join('qf.formula', 'formula', \Doctrine\ORM\Query\Expr\Join::WITH)
                ->andWhere('qf.questionnaire IN (:questionnaires)')
                ->andWhere('qf.part = :part');

        $params = array(
            'questionnaires' => $questionnaires,
            'part' => $part,
        );
        $qb->setParameters($params);

        $where = array();
        foreach ($formulaNames as $i => $word) {
            $parameterName = 'word' . $i;
            $where[] = 'LOWER(formula.name) LIKE LOWER(:' . $parameterName . ')';
            $qb->setParameter($parameterName, '%' . $word . '%');
        }
        $qb->andWhere(join(' OR ', $where));

        $questionnaireFormula = $qb->getQuery()->getResult();

        return $questionnaireFormula;
    }

}
