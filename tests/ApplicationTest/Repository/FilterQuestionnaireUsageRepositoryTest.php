<?php

namespace ApplicationTest\Repository;

class FilterQuestionnaireUsageRepositoryTest extends AbstractRepository
{

    public function testCanSaveConcreteRuleAndReload()
    {
        $survey = new \Application\Model\Survey();
        $survey->setCode('test code');
        $survey->setName('test survey');
        $geoname = new \Application\Model\Geoname();
        $questionnaire = new \Application\Model\Questionnaire();
        $questionnaire->setSurvey($survey)->setGeoname($geoname)->setDateObservationStart(new \DateTime())->setDateObservationEnd(new \DateTime());
        $filter = new \Application\Model\Filter('test filter');
        $part = new \Application\Model\Part('unit test parts');
        $usage = new \Application\Model\Rule\FilterQuestionnaireUsage();
        $rule = new \Application\Model\Rule\Rule();
        $rule->setName('test rule');
        $usage->setJustification('unit tests')
                ->setFilter($filter)
                ->setQuestionnaire($questionnaire)
                ->setRule($rule)
                ->setPart($part);

        $this->getEntityManager()->persist($survey);
        $this->getEntityManager()->persist($geoname);
        $this->getEntityManager()->persist($questionnaire);
        $this->getEntityManager()->persist($filter);
        $this->getEntityManager()->persist($rule);
        $this->getEntityManager()->persist($usage);
        $this->getEntityManager()->persist($part);

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $ruleRepository = $this->getEntityManager()->getRepository('Application\Model\Rule\Rule');
        $loadedRule = $ruleRepository->findOneById($rule->getId());
        $this->assertInstanceOf('Application\Model\Rule\Rule', $loadedRule, 'should be the correct class');

        $this->assertNotSame($rule, $loadedRule, 'should not be same object, since we entirely cleared Doctrine and reloaded a new object');
        $this->assertSame($rule->getId(), $loadedRule->getId(), 'should be same ID');
    }

}
