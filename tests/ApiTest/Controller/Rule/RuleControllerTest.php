<?php

namespace ApiTest\Controller\Rule;

use Zend\Http\Request;
use ApiTest\Controller\AbstractRestfulControllerTest;
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

    public function testCannotUpdateRuleWithAnotherQuestionnaireUsage()
    {
        $questionnaire2 = $this->createAnotherQuestionnaire();
        $usage = new QuestionnaireUsage();
        $usage->setRule($this->rule)->setQuestionnaire($questionnaire2)->setPart($this->part)->setJustification('unit tests');

        // Update should be forbidden, because rule is used in another questionnaire
        $data = array('name' => 'foo');
        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(403);

        // Same for delete
        $this->dispatch($this->getRoute('delete'), Request::METHOD_DELETE);
        $this->assertResponseStatusCode(403);

        // But we still should be able to read it
        $this->dispatch($this->getRoute('get'), Request::METHOD_GET);
        $this->assertResponseStatusCode(200);
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

        // Same for delete
        $this->dispatch($this->getRoute('delete'), Request::METHOD_DELETE);
        $this->assertResponseStatusCode(403);

        // But we still should be able to read it
        $this->dispatch($this->getRoute('get'), Request::METHOD_GET);
        $this->assertResponseStatusCode(200);
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

        // Same for delete
        $this->dispatch($this->getRoute('delete'), Request::METHOD_DELETE);
        $this->assertResponseStatusCode(403);

        // But we still should be able to read it
        $this->dispatch($this->getRoute('get'), Request::METHOD_GET);
        $this->assertResponseStatusCode(200);
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
