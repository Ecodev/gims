<?php

namespace Application\Repository;

class DiscussionRepository extends AbstractRepository
{

    /**
     *
     * @param array $surveys
     * @param array $questionnaires
     * @param array $filters
     * @return \Application\Model\Discussion[]
     */
    public function getAllByParent(array $surveys, array $questionnaires, array $filters)
    {
        $qb = $this->createQueryBuilder('comment');
        $or = $qb->expr()->orX();

        // retrieve discussion associated to surveys
        if ($surveys) {
            $or->add('comment.survey IN (:surveys)');
            $qb->setParameter('surveys', $surveys);
        }

        // retrieve discussion associated to questionnaires
        if ($questionnaires) {
            $or->add('comment.questionnaire IN (:questionnaires) AND comment.filter IS NULL');
            $qb->setParameter('questionnaires', $questionnaires);
        }

        // retrieve discussion associated to filters
        if ($filters) {
            $or->add('comment.questionnaire IN (:questionnaires) AND comment.filter IN (:filters)');
            $qb->setParameter('questionnaires', $questionnaires);
            $qb->setParameter('filters', $filters);
        }

        if ($or->count()) {
            $qb->where($or);
        }

        $qb->addOrderBy('comment.survey', 'ASC');
        $qb->addOrderBy('comment.questionnaire', 'ASC');
        $qb->addOrderBy('comment.filter', 'ASC');
        $qb->addOrderBy('comment.dateCreated', 'ASC');

        return $qb->getQuery()->getResult();
    }

}
