<?php

namespace Application\Repository;

use Doctrine\ORM\Query\Expr\Join;

class QuestionRepository extends AbstractChildRepository
{

    /**
     * Returns all items with read access
     * @return array
     */
    public function getAllWithPermission($action = 'read', $search = null, $parentName = null, \Application\Model\AbstractModel $parent = null)
    {
        $qb = $this->createQueryBuilder('question')
            ->join('question.survey', 'survey', Join::WITH)
            ->where('question.' . $parentName . ' = :parent')
            ->setParameter('parent', $parent)->orderBy('question.sorting')
            ->groupBy('question.id');

        $this->addSearch($qb, $search);
        $this->addPermission($qb, 'survey', \Application\Model\Permission::getPermissionName($this, $action));

        return $qb->getQuery()->getResult();
    }

    /**
     * Changer question type directly in database
     * @todo : find a way to change type with doctrine (transform a doctrine object to another)
     * @param integer $id
     * @param \Application\Model\QuestionType $questionType
     * @return self
     */
    public function changeType($id, \Application\Model\QuestionType $questionType)
    {
        $class = \Application\Model\QuestionType::getClass($questionType);
        $dtype = strtolower(str_replace("Application\\Model\\Question\\", '', $class));

        $sql = "UPDATE question SET dtype='" . $dtype . "' WHERE id=" . $id;
        $this->getEntityManager()->getConnection()->executeUpdate($sql);

        return $this;
    }

    /**
     * Returns all items with read access and answers and choices related
     * @return array
     */
    public function getAllWithPermissionWithAnswers($action = 'read', \Application\Model\Survey $survey = null, array $questionnairesIds = null)
    {
        /** @var \Doctrine\ORM\QueryBuilder $qb */

        // Answerable questions with parts
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('question, chapter, parts')
            ->from('Application\Model\Question\AbstractAnswerableQuestion', 'question')
            ->join('question.survey', 'survey', Join::WITH)
            ->join('question.parts', 'parts')
            ->leftJoin('question.chapter', 'chapter')
            ->where('question.survey = :survey')
            ->setParameter('survey', $survey)
            ->orderBy('question.sorting', 'ASC')
            ->addOrderBy('parts.id', 'ASC')
        ;
        $this->addPermission($qb, 'survey', \Application\Model\Permission::getPermissionName($this, $action));
        $questions = $qb->getQuery()->getArrayResult();

        // Chapters (question without parts)
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('question, chapter')
            ->from('Application\Model\Question\Chapter', 'question')
            ->join('question.survey', 'survey', Join::WITH)
            ->leftJoin('question.chapter', 'chapter')
            ->where('question.survey = :survey')
            ->setParameter('survey', $survey)
            ->orderBy('question.sorting')
        ;
        $this->addPermission($qb, 'survey', \Application\Model\Permission::getPermissionName($this, $action));
        $chapters = $qb->getQuery()->getArrayResult();

        // merge questions and chapters
        $questions = array_merge($chapters, $questions);

        // create question index
        $questionsIndexed = array();
        foreach ($questions as $question) {
            $questionsIndexed[$question['id']] = $question;
            $questionsIndexed[$question['id']]['answers'] = array('1' => array(), '2' => array(), '3' => array());
        }

        // ChoiceQuestions with parts, isMultiple and choices
        $qb = $this->getEntityManager()->createQueryBuilder()
            ->select('question, chapter, parts, choices')
            ->from('Application\Model\Question\ChoiceQuestion', 'question')
            ->join('question.survey', 'survey', Join::WITH)
            ->join('question.parts', 'parts')
            ->leftJoin('question.choices', 'choices')
            ->leftJoin('question.chapter', 'chapter')
            ->where('question.survey = :survey')
            ->setParameter('survey', $survey)
            ->orderBy('question.sorting')
            ->addOrderBy('choices.sorting')
        ;

        // add choices to questions
        $this->addPermission($qb, 'survey', \Application\Model\Permission::getPermissionName($this, $action));
        $choiceQuestions = $qb->getQuery()->getArrayResult();
        foreach ($choiceQuestions as $question) {
            $questionsIndexed[$question['id']]['choices'] = $question['choices'];
            $questionsIndexed[$question['id']]['isMultiple'] = $question['isMultiple'];
        }

        // answers
        $answers = $this->getEntityManager()
            ->getRepository('\Application\Model\Answer')
            ->getAllAnswersInQuestionnaires($survey, $questionnairesIds);

        // add answers to questions
        foreach ($answers as $answer) {
            $answerQuestionId = $answer['question']['id'];
            $answerPartId = $answer['part']['id'];
            $answerQuestionnaireId = $answer['questionnaire']['id'];
            if (!isset($questionsIndexed[$answerQuestionId]['answers'][$answerPartId][$answerQuestionnaireId])) {
                $questionsIndexed[$answerQuestionId]['answers'][$answerPartId][$answerQuestionnaireId] = array($answer);
            } else {
                array_push($questionsIndexed[$answerQuestionId]['answers'][$answerPartId][$answerQuestionnaireId], $answer);
            }
        }

        return $questionsIndexed;
    }

    /**
     * Get one question, without taking into consideration its type
     * @param integer $id
     */
    public function getOneById($id)
    {

        $query = $this->getEntityManager()
            ->createQuery("SELECT q FROM Application\Model\Question\AbstractQuestion q WHERE q.id = :id");

        $params = array(
            'id' => $id,
        );

        $query->setParameters($params);
        $question = $query->getOneOrNullResult();

        return $question;
    }

}
