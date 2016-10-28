<?php

namespace ApplicationTest\Model;

use Application\Model\Answer;
use Application\Model\Filter;
use Application\Model\Geoname;
use Application\Model\Part;
use Application\Model\Question\NumericQuestion;
use Application\Model\Questionnaire;
use Application\Model\Survey;

/**
 * @group Model
 */
class ActivityTest extends AbstractModel
{

    public function testActivitiesAreCreated()
    {
        // logout test user, so we don't need to deal with user mergind when clearing EntityManager
        $this->identityProvider->setIdentity(null);

        $answerRepository = $this->getEntityManager()->getRepository(\Application\Model\Answer::class);
        $activiyRepository = $this->getEntityManager()->getRepository(\Application\Model\Activity::class);

        $survey = new Survey('test survey');
        $survey->setIsActive(true);
        $survey->setCode('code test survey');
        $survey->setYear(2010);

        $geoname = new Geoname('test geoname');
        $filter = new Filter('tst filter');

        $questionnaire = new Questionnaire();
        $questionnaire->setSurvey($survey);
        $questionnaire->setDateObservationStart(new \DateTime('2010-01-01T00:00:00+0100'));
        $questionnaire->setDateObservationEnd(new \DateTime('2011-01-01T00:00:00+0100'));
        $questionnaire->setGeoname($geoname);

        $question = new NumericQuestion('test question');
        $question->setSurvey($survey);
        $question->setFilter($filter);

        $part = new Part('test part 1');
        $this->getEntityManager()->persist($part);
        $this->getEntityManager()->persist($geoname);
        $this->getEntityManager()->persist($survey);
        $this->getEntityManager()->persist($questionnaire);
        $this->getEntityManager()->persist($question);
        $this->getEntityManager()->persist($filter);
        $this->getEntityManager()->flush();

        $this->getEntityManager()->getConnection()->executeQuery('DELETE FROM activity;');
        $answer = new Answer();
        $answer->setQuestion($question)->setQuestionnaire($questionnaire)->setPart($part)->setValuePercent(0.55);
        $this->getEntityManager()->persist($answer);
        $this->getEntityManager()->flush();
        $activities1 = $activiyRepository->findAll();
        $this->assertActivity($activities1, 1, 'create');

        $answer->setValuePercent(0.10);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();
        $reloadedAnswer = $answerRepository->findOneById($answer->getId());
        $this->assertEquals(0.10, $reloadedAnswer->getValuePercent());
        $activities2 = $activiyRepository->findAll();
        $this->assertActivity($activities2, 2, 'update');

        $reloadedAnswer->setValuePercent(0.22);
        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();
        $reloadedAnswer2 = $answerRepository->findOneById($answer->getId());
        $this->assertEquals(0.22, $reloadedAnswer2->getValuePercent());
        $activities3 = $activiyRepository->findAll();
        $this->assertActivity($activities3, 3, 'update');

        $this->getEntityManager()->remove($reloadedAnswer2);
        $this->getEntityManager()->flush();
        $reloadedAnswer3 = $answerRepository->findOneById($answer->getId());
        $this->assertEquals(null, $reloadedAnswer3);
        $activities4 = $activiyRepository->findAll();
        $this->assertActivity($activities4, 4, 'delete');
    }

    private function assertActivity(array $activities, $count, $action)
    {
        $this->assertCount($count, $activities, $action . ' should be recorded');

        /* @var $activity \Application\Model\Activity */
        $activity = end($activities);
        $this->assertEquals($action, $activity->getAction(), 'correct action must be recorded');

        $this->assertTrue(is_numeric($activity->getRecordId()), 'ID must be recorded');
        $this->assertEquals('answer', $activity->getRecordType(), 'type must be recorded');

        if ($action != 'delete') {
            $this->assertArrayHasKey('valuePercent', $activity->getChanges(), 'changed values must be recorded');
        }

        $this->assertArrayHasKey('questionnaire', $activity->getData(), 'should have some data to link to questionnaire');
        $this->assertArrayHasKey('filter', $activity->getData(), 'should have some data to link to filter');
    }
}
