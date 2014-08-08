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

    use \ApiTest\Controller\Traits\ReferenceableInRule;

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

    public function testCannotUpdateRuleWithPublishedQuestionnaire()
    {
        $data = array('name' => 'foo');
        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(201);
        $actual = $this->getJsonResponse();
        $this->assertEquals($data['name'], $actual['name']);

        // Change questionnaire to be published
        $this->questionnaire->setStatus(\Application\Model\QuestionnaireStatus::$PUBLISHED);
        $this->getEntityManager()->merge($this->questionnaire);
        $this->getEntityManager()->flush();

        // Now, the same operation should be forbidden, because the questionnaire is published
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

    public function testCanValidateRuleOnCreation()
    {
        // Rule
        $validData = array(
            'name' => 'new-rule A',
        );
        $expected = [
            'id' => null,
            'name' => 'new-rule A',
            'formula' => '=',
        ];

        $this->dispatch($this->getRoute('post') . '?validate', Request::METHOD_POST, $validData);
        $this->assertResponseStatusCode(200);
        $actual = $this->getJsonResponse();
        $this->assertEquals($expected, $actual, 'new object should not be created, but still returned');

        $invalidData = array(
            'name' => 'new-rule A',
            'formula' => 'invalid syntax',
        );

        $this->dispatch($this->getRoute('post') . '?validate', Request::METHOD_POST, $invalidData);
        $this->assertResponseStatusCode(403);
        $actual = $this->getJsonResponse();
        $this->assertEquals('Object is not valid', $actual['title'], 'error should be returned');
        $this->assertArrayHasKey('messages', $actual, 'messages should be present to detail what is invalid');
    }

    public function testCanValidateRuleOnUpdate()
    {
        // Rule
        $validData = array(
            'name' => 'new-rule A',
            'formula' => '= 1 * 2 * 3',
        );

        $expected = array_merge(['id' => $this->rule->getId()], $validData);

        $this->dispatch($this->getRoute('put') . '?validate', Request::METHOD_PUT, $validData);
        $this->assertResponseStatusCode(200);
        $actual = $this->getJsonResponse();
        $this->assertEquals($expected, $actual, 'returned object must be the modified but non-persisted version');

        $invalidData = array(
            'name' => 'new-rule A',
            'formula' => 'invalid syntax',
        );

        $this->dispatch($this->getRoute('put') . '?validate', Request::METHOD_PUT, $invalidData);
        $this->assertResponseStatusCode(403);
        $actual = $this->getJsonResponse();
        $this->assertEquals('Object is not valid', $actual['title'], 'error should be returned');
        $this->assertArrayHasKey('messages', $actual, 'messages should be present to detail what is invalid');
    }

}
