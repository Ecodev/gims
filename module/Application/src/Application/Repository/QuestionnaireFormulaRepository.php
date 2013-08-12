<?php

namespace Application\Repository;

class QuestionnaireFormulaRepository extends AbstractRepository
{

    public function getOneByFormulaName($formulaName, \Application\Model\Questionnaire $questionnaire, \Application\Model\Part $part)
    {
        $query = $this->getEntityManager()->createQuery('SELECT a
            FROM Application\Model\Rule\QuestionnaireFormula a
            JOIN Application\Model\Rule\Formula f
            WHERE
            a.formula = f
            AND f.name LIKE :ruleName
            AND a.questionnaire = :questionnaire
            AND a.part = :part'
        );

        $params = array(
            'ruleName' => '%' . $formulaName . '%',
            'questionnaire' => $questionnaire,
            'part' => $part,
        );

        $query->setParameters($params);

        $questionnaireFormula = $query->getOneOrNullResult();

        return $questionnaireFormula;
    }

}
