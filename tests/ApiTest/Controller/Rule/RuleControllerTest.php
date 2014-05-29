<?php

namespace ApiTest\Controller\Rule;

use Zend\Http\Request;
use ApiTest\Controller\AbstractRestfulControllerTest;
use Application\Model\Questionnaire;
use Application\Model\Rule\FilterQuestionnaireUsage;
use Application\Model\Rule\QuestionnaireUsage;
use Application\Model\Rule\FilterGeonameUsage;

/**
 * @group Rest
 */
class RuleControllerTest extends AbstractRestfulControllerTest
{

    protected function getAllowedFields()
    {
        return array('id', 'name', 'formula');
    }

    protected function getTestedObject()
    {
        return $this->rule;
    }

    public function testCanUpdateRule()
    {
        $data = array('name' => 'foo');
        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(201);
        $actual = $this->getJsonResponse();
        $this->assertEquals($data['name'], $actual['name']);

        // anonymous
        $this->identityProvider->setIdentity(null);
        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(403);
    }

    /**
     * Create a second questionnaire on which we don't have access
     * @return \Application\Model\Questionnaire
     */
    private function createAnotherQuestionnaire()
    {
        // Reload things from DB
        $this->survey = $this->getEntityManager()->merge($this->survey);
        $this->geoname = $this->getEntityManager()->merge($this->geoname);
        $this->part = $this->getEntityManager()->merge($this->part);
        $this->rule = $this->getEntityManager()->merge($this->rule);

        $questionnaire2 = new Questionnaire();
        $questionnaire2->setSurvey($this->survey);
        $questionnaire2->setDateObservationStart(new \DateTime('2010-01-01T00:00:00+0100'));
        $questionnaire2->setDateObservationEnd(new \DateTime('2011-01-01T00:00:00+0100'));
        $questionnaire2->setGeoname($this->geoname);

        return $questionnaire2;
    }

    public function testCannotUpdateRuleWithAnotherQuestionnaireUsage()
    {
        $questionnaire2 = $this->createAnotherQuestionnaire();
        $usage = new QuestionnaireUsage();
        $usage->setRule($this->rule)->setQuestionnaire($questionnaire2)->setPart($this->part)->setJustification('unit tests');

        // Update should be forbidden, because rule is used in another questionnaire
        $data = array('name' => 'foo');
        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(403);
    }

    public function testCannotUpdateRuleWithAnotherFilterQuestionnaireUsage()
    {
        $questionnaire2 = $this->createAnotherQuestionnaire();
        $usage = new FilterQuestionnaireUsage();
        $usage->setRule($this->rule)->setQuestionnaire($questionnaire2)->setPart($this->part)->setJustification('unit tests');

        // Update should be forbidden, because rule is used in another questionnaire
        $data = array('name' => 'foo');
        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(403);
    }

    public function testCannotUpdateRuleWithAnotherFilterGeonameUsage()
    {
        $this->createAnotherQuestionnaire();
        $usage = new FilterGeonameUsage();
        $usage->setRule($this->rule)->setGeoname($this->geoname)->setPart($this->part)->setJustification('unit tests');

        // Update should be forbidden, because rule is used in another questionnaire
        $data = array('name' => 'foo');
        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(403);
    }

    public function testCanCreateRule()
    {
        // Rule
        $data = array(
            'name' => 'new-rule A',
        );

        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $this->assertResponseStatusCode(201);
        $actual = $this->getJsonResponse();
        $this->assertEquals($data['name'], $actual['name']);

        // anonymous
        $this->identityProvider->setIdentity(null);
        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $this->assertResponseStatusCode(403);
    }

}
