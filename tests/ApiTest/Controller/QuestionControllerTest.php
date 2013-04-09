<?php

namespace ApiTest\Controller;

use Application\Model\Answer;
use Application\Model\Category;
use Application\Model\Geoname;
use Application\Model\Question;
use Application\Model\Questionnaire;
use Application\Model\Part;
use Application\Model\Survey;
use ApplicationTest\Controller\AbstractController;
use Zend\Http\Request;
use Zend\Json\Json;

class QuestionControllerTest extends AbstractController
{

    /**
     * @var Survey
     */
    private $survey;

    /**
     * @var Questionnaire
     */
    private $questionnaire;

    /**
     * @var Question
     */
    private $question;

    /**
     * @var Category
     */
    private $category;

    /**
     * @var Part
     */
    private $part;

    /**
     * @var Answer
     */
    private $answerOne;

    /**
     * @var Answer
     */
    private $answerTwo;

    public function setUp()
    {
        parent::setUp();

        $this->survey = new Survey();
        $this->survey->setActive(true);
        $this->survey->setName('test survey');
        $this->survey->setCode('code test survey');
        $this->survey->setYear(2010);

        $geoName = new Geoname();

        $this->part = new Part();
        $this->part->setName('foo');

        $this->category = new Category();
        $this->category->setName('foo')
            ->setOfficial(true);

        $this->questionnaire = new Questionnaire();
        $this->questionnaire->setSurvey($this->survey);
        $this->questionnaire->setDateObservationStart(new \DateTime('2010-01-01T00:00:00+0100'));
        $this->questionnaire->setDateObservationEnd(new \DateTime('2011-01-01T00:00:00+0100'));
        $this->questionnaire->setGeoname($geoName);

        $this->question = new Question();
        $this->question->setSurvey($this->survey)
            ->setSorting(1)
            ->setType(1)
            ->setCategory($this->category)
            ->setName('foo');

        $this->answerOne = new Answer();
        $this->answerOne
            ->setPart($this->part) // question one has a part whereas question two not.
            ->setQuestion($this->question)
            ->setQuestionnaire($this->questionnaire);

        $this->answerTwo = new Answer();
        $this->answerTwo
            ->setQuestion($this->question)
            ->setQuestionnaire($this->questionnaire);

        $this->getEntityManager()->persist($this->part);
        $this->getEntityManager()->persist($this->category);
        $this->getEntityManager()->persist($geoName);
        $this->getEntityManager()->persist($this->survey);
        $this->getEntityManager()->persist($this->questionnaire);
        $this->getEntityManager()->persist($this->question);
        $this->getEntityManager()->persist($this->answerOne);
        $this->getEntityManager()->persist($this->answerTwo);
        $this->getEntityManager()->flush();
    }

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

    private function getJsonResponse()
    {
        $content = $this->getResponse()->getContent();
        $json = Json::decode($content, Json::TYPE_ARRAY);

        $this->assertTrue(is_array($json));
        return $json;
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
        $allowedFields = array('id', 'name', 'category', 'answers');
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
            'category' => $this->category->getId(),
        );

        $this->dispatch($this->getRoute('post'), Request::METHOD_POST, $data);
        $actual = $this->getJsonResponse();
        $this->assertEquals($data['name'], $actual['name']);
    }
}
