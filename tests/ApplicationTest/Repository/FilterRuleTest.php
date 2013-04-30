<?php

namespace ApplicationTest\Repository;

class FilterRuleRepositoryTest extends AbstractRepository
{

    public function testCanSaveConcreteRuleAndReload()
    {
        $survey = new \Application\Model\Survey();
        $survey->setCode('test code');
        $survey->setName('test survey');
        $geoname = new \Application\Model\Geoname();
        $questionnaire = new \Application\Model\Questionnaire();
        $questionnaire->setSurvey($survey)->setGeoname($geoname)->setDateObservationStart(new \DateTime())->setDateObservationEnd(new \DateTime());
        $filter = new \Application\Model\Filter('test filter filter component');
        $relation = new \Application\Model\Rule\FilterRule();
        $rule = new \Application\Model\Rule\Exclude();
        $relation->setFilter($filter)->setQuestionnaire($questionnaire)->setRule($rule);

        $this->getEntityManager()->persist($survey);
        $this->getEntityManager()->persist($geoname);
        $this->getEntityManager()->persist($questionnaire);
        $this->getEntityManager()->persist($filter);
        $this->getEntityManager()->persist($rule);
        $this->getEntityManager()->persist($relation);

        $this->getEntityManager()->flush();
        $this->getEntityManager()->clear();

        $ruleRepository = $this->getEntityManager()->getRepository('Application\Model\Rule\AbstractRule');
        $loadedRule = $ruleRepository->findOneById($rule->getId());
        $this->assertInstanceOf('Application\Model\Rule\Exclude', $loadedRule, 'should be the correct class');


        $this->assertNotSame($rule, $loadedRule, 'should not be same object, since we entirely cleared Doctrine and reloaded a new object');
        $this->assertSame($rule->getId(), $loadedRule->getId(), 'should be same ID');
    }

}
