<?php

namespace ApiTest\Controller;

use Zend\Http\Request;

class AnswerControllerTest extends AbstractController
{

    /**
     * Get suitable route for GET method.
     *
     * @param string $method
     *
     * @return string
     */
    private function getRoute($method)
    {
        switch ($method) {
            case 'get':
                $route = sprintf(
                        '/api/answer/%s', $this->answer->getId()
                );
                break;
            case 'post':
                $route = '/api/answer';
                break;
            case 'put':
                $route = sprintf(
                        '/api/answer/%s', $this->answer->getId()
                );
                break;
            default:
                $route = '';
        }

        return $route;
    }

    /**
     * @test
     * @group AnswerApi
     */
    public function dispatchRouteForAnswerReturnsStatus200()
    {
        $this->dispatch($this->getRoute('get'), Request::METHOD_GET);
        $this->assertResponseStatusCode(200);
    }

    /**
     * @test
     * @group AnswerApi
     */
    public function ensureOnlyAllowedFieldAreDisplayedInResponseForAnswer()
    {
        $this->dispatch($this->getRoute('get'), Request::METHOD_GET);
        $allowedFields = array('id', 'valuePercent', 'valueAbsolute', 'valueText', 'isCheckboxChecked', 'valueChoice', 'part', 'question', 'valueUser');
        foreach ($this->getJsonResponse() as $key => $value) {
            $this->assertTrue(in_array($key, $allowedFields));
        }
    }

    /**
     * @test
     * @group AnswerApi
     */
    public function getFakeAnswerAndCheckWhetherIdsAreCorresponding()
    {
        $this->dispatch($this->getRoute('get'), Request::METHOD_GET);
        $actual = $this->getJsonResponse();
        $this->assertSame($this->answer->getId(), $actual['id']);
    }

    /**
     * @test
     * @group AnswerApi
     */
    public function updateAnswerAndCheckWhetherValuePercentIsDifferentFromOriginalValue()
    {
        $expected = $this->answer->getValuePercent();
        $data = array(
            'valuePercent' => 0.2,
        );

        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $actual = $this->getJsonResponse();
        $this->assertNotEquals($expected, $actual['valuePercent']);
    }

    /**
     * @test
     * @group AnswerApi
     */
    public function canUpdateValuePercentOfAnswer()
    {
        $expected = $this->answer->getValuePercent() + 0.2;
        $data = array(
            'valuePercent' => $expected,
        );

        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $actual = $this->getJsonResponse();
        $this->assertEquals($expected, $actual['valuePercent']);
    }

    /**
     * @test
     * @group AnswerApi
     */
    public function updateAnAnswerWillReturn201AsCode()
    {
        $expected = $this->answer->getValuePercent() + 0.2;
        $data = array(
            'valuePercent' => $expected,
        );

        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(201);
    }

    /**
     * @test
     * @group AnswerApi
     */
    public function postANewAnswerWithNestedObjectWillCreateIt()
    {
        // Question
        $data = array(
            'valuePercent' => 0.6,
            'question' => array(
                'id' => $this->question->getId()
            ),
            'questionnaire' => array(
                'id' => $this->questionnaire->getId()
            ),
            'part' => array(
                'id' => $this->part3->getId()
            ),
        );

        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $actual = $this->getJsonResponse();
        $this->assertEquals($data['valuePercent'], $actual['valuePercent']);
    }

    /**
     * @test
     * @group AnswerApi
     */
    public function postANewAnswerWithFlatObjectWillCreateIt()
    {
        // Question
        $data = array(
            'valuePercent' => 0.6,
            'question' => $this->question->getId(),
            'questionnaire' => $this->questionnaire->getId(),
            'part' => $this->part3->getId(),
        );

        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $actual = $this->getJsonResponse();
        $this->assertEquals($data['valuePercent'], $actual['valuePercent']);
    }

    /**
     * @test
     * @group AnswerApi
     */
    public function postANewAnswerReturnsStatusCode403ForUserWithRoleAnonymous()
    {
        // Question
        $data = array(
            'valuePercent' => 0.6,
            'question' => array(
                'id' => $this->question->getId()
            ),
            'questionnaire' => array(
                'id' => $this->questionnaire->getId()
            ),
            'part' => array(
                'id' => $this->part->getId()
            ),
        );

        $this->rbac->setIdentity(null);
        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $this->assertResponseStatusCode(403);
    }

    /**
     * @test
     * @group AnswerApi
     */
    public function postANewAnswerReturnsStatusCode201ForUserWithRoleReporter()
    {
        // Question
        $data = array(
            'valuePercent' => 0.6,
            'question' => array(
                'id' => $this->question->getId()
            ),
            'questionnaire' => array(
                'id' => $this->questionnaire->getId()
            ),
            'part' => array(
                'id' => $this->part3->getId()
            ),
        );

        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $this->assertResponseStatusCode(201);
    }

    /**
     * @test
     * @group AnswerApi
     */
    public function postANewAnswerReturnsSpecifiedFields()
    {
        // Question
        $data = array(
            'valuePercent' => 0.6,
            'question' => array(
                'id' => $this->question->getId()
            ),
            'questionnaire' => array(
                'id' => $this->questionnaire->getId()
            ),
            'part' => array(
                'id' => $this->part3->getId()
            ),
        );

        $this->dispatch($this->getRoute('post') . '?fields=questionnaire', Request::METHOD_POST, $data);
        $this->assertResponseStatusCode(201);
        $actual = $this->getJsonResponse();
        $this->assertEquals($data['questionnaire']['id'], $actual['questionnaire']['id']);
    }

    /**
     * @test
     * @group AnswerApi
     */
    public function updateAnAnswerAsAnonymousReturnsStatusCode403()
    {
        $expected = $this->answer->getValuePercent() + 0.2;
        $data = array(
            'valuePercent' => $expected,
        );

        $this->rbac->setIdentity(null);
        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(403);
    }

    /**
     * @test
     * @group AnswerApi
     */
    public function updateAnAnswerWithRoleReporterReturnsStatusCode201()
    {
        $expected = $this->answer->getValuePercent() + 0.2;
        $data = array(
            'valuePercent' => $expected,
        );

        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(201);
    }

    /**
     * @test
     * @group AnswerApi
     */
    public function updateAnAnswerReturnsSpecifiedFields()
    {
        $expected = $this->answer->getValuePercent() + 0.2;
        $data = array(
            'valuePercent' => $expected,
        );

        $this->dispatch($this->getRoute('put') . '?fields=questionnaire', Request::METHOD_PUT, $data);
        $this->assertResponseStatusCode(201);
        $actual = $this->getJsonResponse();
        $this->assertEquals($this->answer->getQuestionnaire()->getId(), $actual['questionnaire']['id']);
    }

}
