<?php

namespace Application\Repository;

class QuestionnaireRuleRepository extends AbstractRepository
{

    public function getOneByRuleName($ruleName, \Application\Model\Questionnaire $questionnaire, \Application\Model\Part $part)
    {
        $query = $this->getEntityManager()->createQuery('SELECT a
            FROM Application\Model\Rule\QuestionnaireRule a
            JOIN Application\Model\Rule\AbstractRule r
            WHERE
            a.rule = r
            AND r.name LIKE :ruleName
            AND a.questionnaire = :questionnaire
            AND a.part = :part'
        );

        $params = array(
            'ruleName' => '%' . $ruleName . '%',
            'questionnaire' => $questionnaire,
            'part' => $part,
        );

        $query->setParameters($params);

        $questionnaireRule = $query->getOneOrNullResult();

        return $questionnaireRule;
    }

}
