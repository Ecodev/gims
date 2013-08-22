<?php

namespace ApiTest\Controller;

use Application\Model\Question\NumericQuestion;
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
        $allowedFields = array('id', 'name', 'filter', 'answers', 'sorting');
        foreach ($this->getJsonResponse() as $key => $value) {
            $this->assertTrue(in_array($key, $allowedFields), "the key '$key' should not be in response");
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
        $this->dispatch($this->getRoute('get') . '?fields=answers', Request::METHOD_GET);
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
            'type'     => \Application\Model\QuestionType::$CHOICE,
            'sorting'  => 1,
            'survey'   => $this->survey->getId(),
            'filter' => $this->filter->getId(),
            'type' => \Application\Model\QuestionType::$NUMERIC,
        );

        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $actual = $this->getJsonResponse();
        $this->assertEquals($data['name'], $actual['name']);
    }

    /**
     * @test
     */
    public function countNumberOfQuestionsReturnsByMethodGetQuestionsReturnsOne()
    {
        $this->assertCount(1, $this->survey->getQuestions());
    }

    private function getMockQuestions(){

        // create four additional questions next to the one created in the abstract
        // -> they will be five questions connected to a survey
        $questions[1] = $this->question;
        foreach (array(2, 3, 4, 5) as $value) {

            $question = new NumericQuestion();
            $question->setSurvey($this->survey)
                ->setSorting($value)
                ->setFilter($this->filter)
                ->setName('bar');

            $this->getEntityManager()->persist($question);
            $questions[$value] = $question;
        }
        $this->getEntityManager()->flush();
        $this->assertCount(5, $this->survey->getQuestions());

        return $questions;
    }

    /**
     * @test
     */
    public function moveSortingValueOfLastQuestionToFirstAndCheckWhetherSortingValueOfOtherQuestionsAreShifted()
    {

        $questions = $this->getMockQuestions();

        $route = $route = sprintf(
            '/api/question?id=%s',
            $questions[5]->getId()
        );

        $data = array(
            'sorting' => 1,
        );

        $this->dispatch($route, Request::METHOD_PUT, $data);

        $expectedSorting = array(2, 3, 4, 5, 1);
        $actualSorting = array();
        foreach ($questions as $question) {
            $actualSorting []= $question->getSorting();
        }

        $this->assertSame($expectedSorting, $actualSorting);
    }

    /**
     * @test
     */
    public function moveSortingValueOfFirstQuestionToLastAndCheckWhetherSortingValueOfOtherQuestionsAreShifted()
    {

        $questions = $this->getMockQuestions();

        $route = $route = sprintf(
            '/api/question?id=%s',
            $questions[1]->getId()
        );

        $data = array(
            'sorting' => 5,
        );

        $this->dispatch($route, Request::METHOD_PUT, $data);

        $expectedSorting = array(5, 1, 2, 3, 4);
        $actualSorting = array();
        foreach ($questions as $question) {
            $actualSorting []= $question->getSorting();
        }

        $this->assertSame($expectedSorting, $actualSorting);
    }
}
