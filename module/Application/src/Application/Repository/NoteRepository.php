<?php

namespace Application\Repository;

class NoteRepository extends AbstractRepository
{

    public function getAllByParent(array $surveys, array $questionnaires, array $questions)
    {
        $qb = $this->createQueryBuilder('note');

        // retrieve notes associated to surveys
        $whereClauses = array();

        if (count($surveys) > 0) {
            foreach ($surveys as $surveyId) {
                $whereClauses[] = $qb->expr()->andX(
                    $qb->expr()->eq('note.survey', $surveyId),
                    $qb->expr()->isNull('note.questionnaire'),
                    $qb->expr()->isNull('note.question')
                );
            }
        }

        // retrieve notes associated to questionnaires
        if (count($questionnaires) > 0) {
                foreach ($questionnaires as $questionnaireId) {
                $whereClauses[] =  $qb->expr()->andX(
                    $qb->expr()->eq('note.questionnaire', $questionnaireId),
                    $qb->expr()->isNull('note.survey'),
                    $qb->expr()->isNull('note.question')
                );
            }
        }

        // retrieve notes associated to questions
        if (count($questions) > 0) {
            foreach ($questions as $questionId) {
                $whereClauses[] =$qb->expr()->andX(
                    $qb->expr()->eq('note.question', $questionId),
                    $qb->expr()->isNull('note.survey'),
                    $qb->expr()->isNull('note.questionnaire')
                );
            }
        }

        // retrieve notes associated to the questions that are associated to questionnaires
        if (count($questionnaires) > 0 && count($questions) > 0) {
            foreach ($questionnaires as $questionnaireId) {
                foreach ($questions as $questionId) {
                    $whereClauses[] = $qb->expr()->andX(
                        $qb->expr()->eq('note.question', $questionId),
                        $qb->expr()->eq('note.questionnaire', $questionnaireId),
                        $qb->expr()->isNull('note.survey')
                    );
                }
            }
        }

        $or = $qb->expr()->orx();
        foreach ($whereClauses as $whereClause) {
            $or->add($whereClause);
        }

        $qb->where($or);

        $qb->addOrderBy('note.survey', 'ASC');
        $qb->addOrderBy('note.questionnaire', 'ASC');
        $qb->addOrderBy('note.question', 'ASC');

        return $qb->getQuery()->getResult();
    }

}
