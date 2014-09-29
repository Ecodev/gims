<?php

namespace ApplicationTest\Repository;

/**
 * @group Repository
 */
class QuestionRepositoryTest extends AbstractRepository
{

    public function testCanSaveSaveAndLoadAlternateNamesInJSON()
    {
        $survey = new \Application\Model\Survey('tst survey');
        $survey->setCode('tst');
        $filter = new \Application\Model\Filter('tst filter');

        $question = new \Application\Model\Question\NumericQuestion('tst question');
        $question->setSurvey($survey);
        $question->setFilter($filter);
        $alternateNames = array(
            2 => 'alternate name 2',
            6 => 'alternate name 6',
        );
        $question->setAlternateNames($alternateNames);

        $this->getEntityManager()->persist($survey);
        $this->getEntityManager()->persist($filter);
        $this->getEntityManager()->persist($question);
        $this->getEntityManager()->flush();

        $idQuestion = $question->getId();
        $this->getEntityManager()->clear();

        $repository = $this->getEntityManager()->getRepository('Application\Model\Question\NumericQuestion');
        $reloadedQuestion = $repository->find($idQuestion);
        $this->assertNotSame($question, $reloadedQuestion, 'question should be reloaded from database and thus be different obejct');
        $this->assertEquals($alternateNames, $reloadedQuestion->getAlternateNames(), 'should be able to get the exact same JSON array from database');
    }

}
