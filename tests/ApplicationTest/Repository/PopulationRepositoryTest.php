<?php

namespace ApplicationTest\Repository;

class PopulationRepositoryTest extends AbstractRepository
{

    public function testCanSaveAllQuestionnaireStatusesInDatabase()
    {
        $survey = new \Application\Model\Survey();
        $survey->setCode('test code');
        $survey->setName('test survey');
        $survey->setYear(2000);

        $country = $this->getEntityManager()->getRepository('Application\Model\Country')->findOneBy(['iso3' => 'CHE']);
        $geoname = $country->getGeoname();

        $questionnaire = new \Application\Model\Questionnaire();
        $questionnaire->setSurvey($survey)->setGeoname($geoname)->setDateObservationStart(new \DateTime())->setDateObservationEnd(new \DateTime());

        $part = new \Application\Model\Part('tst part');
        $p1 = new \Application\Model\Population();
        $p1
                ->setCountry($country)
                ->setPart($part)
                ->setPopulation(500)
                ->setYear(2000)
                ->setQuestionnaire($questionnaire)
        ;
        $p2 = new \Application\Model\Population();
        $p2
                ->setCountry($country)
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
        $pop = $populationRepository->getOneByGeoname($geoname, $part->getId(), 2000);
        $this->assertEquals(5, $pop->getPopulation(), 'should return official population');

        $pop = $populationRepository->getOneByGeoname($geoname, $part->getId(), 2000, 12345);
        $this->assertEquals(5, $pop->getPopulation(), 'should default to official population if questionnaire does not have population');

        $pop = $populationRepository->getOneByGeoname($geoname, $part->getId(), 2000, $questionnaire->getId());
        $this->assertEquals(500, $pop->getPopulation(), 'should return non-official population specific the questionnaire');

        $pop = $populationRepository->getOneByQuestionnaire($questionnaire, $part->getId(), 2000);
        $this->assertEquals(500, $pop->getPopulation(), 'should return non-official population specific the questionnaire');
    }

}
