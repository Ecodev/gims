<?php

namespace ApplicationTest\Repository;

/**
 * @group Repository
 */
class PopulationRepositoryTest extends AbstractRepository
{

    public function testCanSaveAllQuestionnaireStatusesInDatabase()
    {
        $survey = new \Application\Model\Survey('test survey');
        $survey->setCode('test code');
        $survey->setYear(2000);

        $geoname = $this->getEntityManager()->getRepository('Application\Model\Geoname')->findOneBy(['iso3' => 'CHE']);

        $questionnaire = new \Application\Model\Questionnaire();
        $questionnaire->setSurvey($survey)->setGeoname($geoname)->setDateObservationStart(new \DateTime())->setDateObservationEnd(new \DateTime());

        $part = new \Application\Model\Part('tst part');
        $p1 = new \Application\Model\Population();
        $p1
                ->setGeoname($geoname)
                ->setPart($part)
                ->setPopulation(500)
                ->setYear(2000)
                ->setQuestionnaire($questionnaire)
        ;
        $p2 = new \Application\Model\Population();
        $p2
                ->setGeoname($geoname)
                ->setPart($part)
                ->setPopulation(5)
                ->setYear(2000)
        ;

        $this->getEntityManager()->persist($part);
        $this->getEntityManager()->persist($p1);
        $this->getEntityManager()->persist($p2);
        $this->getEntityManager()->persist($survey);
        $this->getEntityManager()->persist($questionnaire);
        $this->getEntityManager()->flush();

        $populationRepository = $this->getEntityManager()->getRepository('Application\Model\Population');
        $pop = $populationRepository->getPopulationByGeoname($geoname, $part->getId(), 2000);
        $this->assertEquals(5, $pop, 'should return official population');

        $pop = $populationRepository->getPopulationByGeoname($geoname, $part->getId(), 2000, 12345);
        $this->assertEquals(5, $pop, 'should default to official population if questionnaire does not have population');

        $pop = $populationRepository->getPopulationByGeoname($geoname, $part->getId(), 2000, $questionnaire->getId());
        $this->assertEquals(500, $pop, 'should return non-official population specific the questionnaire');

        $pop = $populationRepository->getPopulationByQuestionnaire($questionnaire, $part->getId(), 2000);
        $this->assertEquals(500, $pop, 'should return non-official population specific the questionnaire');
    }

}
