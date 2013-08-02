<?php

namespace Application\Repository;

class QuestionnaireRuleRepository extends AbstractRepository
{

    public function getOneByRuleName($ruleName, \Application\Model\Questionnaire $questionnaire, \Application\Model\Part $part = null)
    {
        $query = $this->getEntityManager()->createQuery('SELECT a
            FROM Application\Model\Rule\QuestionnaireRule a
            JOIN Application\Model\Rule\AbstractRule r
            WHERE
            a.rule = r
            AND r.name LIKE :ruleName
            AND a.questionnaire = :questionnaire
            AND (a.part ' . ($part ? '= :part' : 'IS NULL') . ')'
        );

        $params = array(
            'ruleName' => '%' . $ruleName . '%',
            'questionnaire' => $questionnaire,
        );

        if ($part)
            $params['part'] = $part;

        $query->setParameters($params);

        $ratios = $query->getOneOrNullResult();

        return $ratios;
    }

}
