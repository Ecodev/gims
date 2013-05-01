<?php

namespace Application\Repository;

class FilterRuleRepository extends AbstractRepository
{

    public function getRatios(\Application\Model\Questionnaire $questionnaire, \Application\Model\Filter $filter, \Application\Model\Part $part = null)
    {
        $query = $this->getEntityManager()->createQuery('SELECT a
            FROM Application\Model\Rule\FilterRule a
            JOIN Application\Model\Rule\Ratio r
            WHERE
            a.rule = r
            AND a.questionnaire = :questionnaire
            AND a.filter = :filter
            AND (a.part ' . ($part ? '= :part' : 'IS NULL') . ')'
            );

        $params = array(
            'questionnaire' => $questionnaire,
            'filter' => $filter,
        );

        if ($part)
            $params['part'] = $part;

        $query->setParameters($params);

        $ratios = $query->getResult();

        return $ratios;
    }

}
