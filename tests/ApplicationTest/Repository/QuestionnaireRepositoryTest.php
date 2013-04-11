<?php

namespace ApplicationTest\Repository;

use \Application\Model\QuestionnaireStatus;

class QuestionnaireRepositoryTest extends AbstractRepository
{

    public function testCanSaveAllQuestionnaireStatusesInDatabase()
    {
        $survey = new \Application\Model\Survey();
        $survey->setCode('test code');
        $survey->setName('test survey');
        $geoname = new \Application\Model\Geoname();
        $questionnaire = new \Application\Model\Questionnaire();
        $questionnaire->setSurvey($survey)->setGeoname($geoname)->setDateObservationStart(new \DateTime())->setDateObservationEnd(new \DateTime());
        $this->getEntityManager()->persist($survey);
        $this->getEntityManager()->persist($geoname);
        $this->getEntityManager()->persist($questionnaire);

        $this->assertEquals(QuestionnaireStatus::$NEW, $questionnaire->getStatus(), 'new questionnaire should have a satus of new');


        // Test each status, that should not throw any exception
        foreach (QuestionnaireStatus::getValues() as $status) {
            $questionnaire->setStatus($status);
            $this->getEntityManager()->flush();
        }

        // Test we can reload
        $this->getEntityManager()->clear();
        $questionnaireRepository = $this->getEntityManager()->getRepository('Application\Model\Questionnaire');
        $loadedQuestionnaire = $questionnaireRepository->findOneById($questionnaire->getId());
        $this->assertSame(QuestionnaireStatus::$REJECTED, $loadedQuestionnaire->getStatus(), 'loaded questionnaire from database, should return an enum object (not a string)');
    }

}
