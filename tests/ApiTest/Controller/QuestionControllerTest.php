<?php

namespace ApiTest\Controller;

use Zend\Http\Request;

class QuestionControllerTest extends AbstractController
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
                    '/api/questionnaire/%s/question/%s',
                    $this->questionnaire->getId(),
                    $this->question->getId()
                );
                break;
            case 'post':
                $route = sprintf(
                    '/api/questionnaire/%s/question',
                    $this->questionnaire->getId()
                );
                break;
            case 'put':
                $route = sprintf(
                    '/api/questionnaire/%s/question?id=%s',
                    $this->questionnaire->getId(),
                    $this->question->getId()
                );
                break;
            default:
                $route = '';

        }
        return $route;
    }

    /**
     * @test
     */
    public function methodGetReturnsStatus200()
    {
        $this->dispatch($this->getRoute('get'), Request::METHOD_GET);
        $this->assertResponseStatusCode(200);
    }

    /**
     * @test
     * @group AnswerModel
     */
    public function ensureOnlyAllowedFieldAreDisplayedInResponseForQuestion()
    {
        $this->dispatch($this->getRoute('get'), Request::METHOD_GET);
        $allowedFields = array('id', 'name', 'filter', 'answers');
        foreach ($this->getJsonResponse() as $key => $value) {
            $this->assertTrue(in_array($key, $allowedFields));
        }
    }

    /**
     * @test
     */
    public function getFakeQuestionAndCheckWhetherIdAreCorresponding()
    {
        $this->dispatch($this->getRoute('get'), Request::METHOD_GET);
        $actual = $this->getJsonResponse();
        $this->assertSame($this->question->getId(), $actual['id']);
    }

    /**
     * @test
     */
    public function getTheFakeQuestionAndCheckWhetherItContainsTwoAnswers()
    {
        $this->dispatch($this->getRoute('get'), Request::METHOD_GET);
        $actual = $this->getJsonResponse();
        $this->assertCount(2, $actual['answers']);
    }

    /**
     * @test
     */
    public function updateQuestionAndCheckWhetherOriginalNameIsChanged()
    {
        $expected = $this->question->getName();
        $data = array(
            'name' => 'bar',
        );

        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $actual = $this->getJsonResponse();
        $this->assertNotEquals($expected, $actual['name']);
    }

    /**
     * @test
     */
    public function canUpdateNameOfQuestion()
    {
        $expected = 'bar';
        $data = array(
            'name' => $expected,
        );

        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $actual = $this->getJsonResponse();
        $this->assertEquals($expected, $actual['name']);
    }

    /**
     * @test
     */
    public function createANewQuestionWithPostMethod()
    {

        // Question
        $data = array(
            'name'     => 'name for test create a new question',
            'type'     => 1,
            'sorting'  => 1,
            'survey'   => $this->survey->getId(),
            'filter' => $this->filter->getId(),
        );

        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $actual = $this->getJsonResponse();
        $this->assertEquals($data['name'], $actual['name']);
    }
}
