<?php

namespace Application\Repository;

use Doctrine\ORM\Query\Expr\Join;

class DiscussionRepository extends AbstractRepository
{

    /**
     *
     * @param array $surveys
     * @param array $questionnaires
     * @param array $filters
     * @return \Application\Model\Discussion[]
     */
    public function getAllByParent(array $surveys, array $questionnaires, array $filters, $search)
    {
        $qb = $this->createQueryBuilder('discussion');
        $qb->leftJoin('discussion.survey', 'survey', Join::WITH);
        $qb->leftJoin('discussion.questionnaire', 'questionnaire', Join::WITH);
        $qb->leftJoin('questionnaire.geoname', 'geoname', Join::WITH);
        $qb->leftJoin('questionnaire.survey', 'survey2', Join::WITH);
        $qb->leftJoin('discussion.filter', 'filter', Join::WITH);
        $or = $qb->expr()->orX();

        // retrieve discussion associated to surveys
        if ($surveys) {
            $or->add('discussion.survey IN (:surveys)');
            $qb->setParameter('surveys', $surveys);
        }

        // retrieve discussion associated to questionnaires
        if ($questionnaires) {
            $or->add('discussion.questionnaire IN (:questionnaires) AND discussion.filter IS NULL');
            $qb->setParameter('questionnaires', $questionnaires);
        }

        // retrieve discussion associated to filters
        if ($filters) {
            $or->add('discussion.questionnaire IN (:questionnaires) AND discussion.filter IN (:filters)');
            $qb->setParameter('questionnaires', $questionnaires);
            $qb->setParameter('filters', $filters);
        }

        if ($or->count()) {
            $qb->where($or);
        }

        $this->addSearch($qb, $search, ['survey.code', 'survey2.code', 'geoname.name', 'filter.name']);

        $qb->addOrderBy('discussion.survey', 'ASC');
        $qb->addOrderBy('discussion.questionnaire', 'ASC');
        $qb->addOrderBy('discussion.filter', 'ASC');
        $qb->addOrderBy('discussion.dateCreated', 'ASC');

        return $qb->getQuery()->getResult();
    }

}
