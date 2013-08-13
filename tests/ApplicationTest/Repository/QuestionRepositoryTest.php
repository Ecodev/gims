<?php

namespace ApplicationTest\Repository;

use \Application\Model\QuestionType;

class QuestionRepositoryTest extends AbstractRepository
{

    public function testCanSaveAllQuestionTypesInDatabase()
    {
        $filter = new \Application\Model\Filter('test filter');
        $survey = new \Application\Model\Survey();
        $survey->setCode('test code');
        $survey->setName('test survey');
        $question = new \Application\Model\Question('test question');
        $question->setSurvey($survey);
        $question->setFilter($filter);
        $question->setSorting(1);
        $this->getEntityManager()->persist($filter);
        $this->getEntityManager()->persist($survey);
        $this->getEntityManager()->persist($question);

        $this->assertEquals(QuestionType::$NUMERIC, $question->getType(), 'new question should have a type of numeric');


        // Test each status, that should not throw any exception
        foreach (QuestionType::getValues() as $type) {
            $question->setType($type);
            $this->getEntityManager()->flush();
        }

        // Test we can reload
        $this->getEntityManager()->clear();
        $questionRepository = $this->getEntityManager()->getRepository('Application\Model\Question');
        $loadedQuestion = $questionRepository->findOneById($question->getId());
        $this->assertSame(end(QuestionType::getValues()), $loadedQuestion->getType(), 'loaded question from database, should return an enum object (not a string)');
    }

}
