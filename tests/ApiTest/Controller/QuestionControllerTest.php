<?php

namespace ApiTest\Controller;

use Application\Model\Question\NumericQuestion;
use Zend\Http\Request;

/**
 * @group Rest
 */
class QuestionControllerTest extends AbstractRestfulControllerTest
{

    protected function getAllowedFields()
    {
        return array('id', 'name', 'filter', 'answers', 'sorting');
    }

    protected function getTestedObject()
    {
        return $this->question;
    }

    /**
     * Get suitable route for GET method.
     *
     * @param string $method
     *
     * @return string
     */
    protected function getRoute($method)
    {
        switch ($method) {
            case 'get':
                $route = sprintf(
                        '/api/questionnaire/%s/question/%s', $this->questionnaire->getId(), $this->question->getId()
                );
                break;
            case 'post':
                $route = sprintf(
                        '/api/questionnaire/%s/question', $this->questionnaire->getId()
                );
                break;
            case 'put':
                $route = sprintf(
                        '/api/questionnaire/%s/question/%s', $this->questionnaire->getId(), $this->question->getId()
                );
                break;
            default:
                $route = '';
        }

        return $route;
    }

    public function testCanRetrieveQuestionAnswers()
    {
        $this->dispatch($this->getRoute('get') . '?fields=answers', Request::METHOD_GET);
        $actual = $this->getJsonResponse();
        $this->assertCount(2, $actual['answers']);
    }

    public function testCanUpdateNameOfQuestion()
    {
        $expected = 'bar';
        $data = array(
            'name' => $expected,
        );

        $this->dispatch($this->getRoute('put'), Request::METHOD_PUT, $data);
        $actual = $this->getJsonResponse();
        $this->assertEquals($expected, $actual['name']);
    }

    public function testCanModifyQuestionType()
    {
        $data = array(
            'type' => \Application\Model\QuestionType::$CHAPTER,
        );

        $url = $this->getRoute('put') . '?fields=type';
        $this->dispatch($url, Request::METHOD_PUT, $data);
        $afterChange = $this->getJsonResponse();
        $this->assertEquals($data['type'], $afterChange['type'], 'should return new type');
    }

    private function getMockQuestions()
    {
        // create four additional questions next to the one created in the abstract
        // -> they will be five questions connected to a survey
        $questions[1] = $this->getEntityManager()->merge($this->question);
        foreach (array(2, 3, 4, 5) as $value) {

            $filter = new \Application\Model\Filter('tst filter ' . $value);
            $question = new NumericQuestion();
            $question->setSurvey($this->getEntityManager()->merge($this->survey))
                    ->setSorting($value)
                    ->setFilter($filter)
                    ->setName('bar');

            $this->getEntityManager()->persist($filter);
            $this->getEntityManager()->persist($question);
            $questions[$value] = $question;
        }

        $this->getEntityManager()->flush();
        $this->assertCount(5, $this->getEntityManager()->merge($this->survey)->getQuestions());

        return $questions;
    }

    public function testMoveSortingValueOfLastQuestionToFirstAndCheckWhetherSortingValueOfOtherQuestionsAreShifted()
    {

        $questions = $this->getMockQuestions();

        $route = $route = sprintf(
                '/api/question?id=%s', $questions[5]->getId()
        );

        $data = array(
            'sorting' => 1,
        );

        $this->dispatch($route, Request::METHOD_PUT, $data);

        $expectedSorting = array(2, 3, 4, 5, 1);
        $actualSorting = array();
        foreach ($questions as $question) {
            $actualSorting [] = $question->getSorting();
        }

        $this->assertSame($expectedSorting, $actualSorting);
    }

    public function testMoveSortingValueOfFirstQuestionToLastAndCheckWhetherSortingValueOfOtherQuestionsAreShifted()
    {

        $questions = $this->getMockQuestions();

        $route = $route = sprintf(
                '/api/question?id=%s', $questions[1]->getId()
        );

        $data = array(
            'sorting' => 5,
        );

        $this->dispatch($route, Request::METHOD_PUT, $data);

        $expectedSorting = array(5, 1, 2, 3, 4);
        $actualSorting = array();
        foreach ($questions as $question) {
            $actualSorting [] = $question->getSorting();
        }

        $this->assertSame($expectedSorting, $actualSorting);
    }

    public function testCreatingQuestionWithChoices()
    {
        $filter = new \Application\Model\Filter('tst filter ');
        $this->getEntityManager()->persist($filter);
        $this->getEntityManager()->flush();

        // Question
        $data = array(
            'name' => 'Question with choices',
            'type' => \Application\Model\QuestionType::$CHOICE,
            'survey' => $this->survey->getId(),
            'filter' => $filter->getId(),
            'choices' => array(
                array(
                    'name' => 'choice 1',
                    'value' => 0.9,
                ),
                array() // This is an empty choice, which must be ignored
            )
        );

        $this->dispatch($this->getRoute('post') . '?fields=type,choices', Request::METHOD_POST, $data);
        $actual = $this->getJsonResponse();

        $this->assertEquals($data['name'], $actual['name']);
        $this->assertEquals($data['type'], $actual['type']);
        $this->assertCount(1, $actual['choices']);
        $this->assertEquals($data['choices'][0]['name'], $actual['choices'][0]['name']);
        $this->assertEquals($data['choices'][0]['value'], $actual['choices'][0]['value']);
    }

}
