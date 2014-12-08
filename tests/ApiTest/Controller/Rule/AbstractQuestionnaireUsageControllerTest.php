<?php

namespace ApiTest\Controller\Rule;

use Zend\Http\Request;
use ApiTest\Controller\AbstractChildRestfulControllerTest;

/**
 * @group Rest
 */
abstract class AbstractQuestionnaireUsageControllerTest extends AbstractChildRestfulControllerTest
{

    public function testCanUpdateRuleWithPublishedQuestionnaire()
    {
        $data = array('justification' => 'foo');
        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(201);
        $actual = $this->getJsonResponse();
        $this->assertEquals($data['justification'], $actual['justification']);

        // Change questionnaire to be published
        $this->questionnaire->setStatus(\Application\Model\QuestionnaireStatus::$PUBLISHED);
        $this->getEntityManager()->merge($this->questionnaire);
        $this->getEntityManager()->flush();

        // Now, the same operation should be forbidden, because the questionnaire is published
        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(201);
    }

}
