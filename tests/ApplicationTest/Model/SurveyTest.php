<?php

namespace ApplicationTest\Model;

use Application\Model\Survey;
use Application\Model\Question\NumericQuestion;
use Application\Model\Questionnaire;

class SurveyTest extends AbstractModel
{

    public function testQuestionsRelation()
    {
        $survey = new Survey();
        $question = new NumericQuestion();

        $this->assertCount(0, $survey->getQuestions(), 'collection is initialized on creation');

        $question->setSurvey($survey);
        $this->assertCount(1, $survey->getQuestions(), 'survey must be notified when question is added');
        $this->assertSame($question, $survey->getQuestions()->first(), 'original question can be retreived from survey');
    }

    public function testQuestionnairesRelation()
    {
        $survey = new Survey();
        $questionnaire = new Questionnaire();

        $this->assertCount(0, $survey->getQuestionnaires(), 'collection is initialized on creation');

        $questionnaire->setSurvey($survey);
        $this->assertCount(1, $survey->getQuestionnaires(), 'survey must be notified when questionnaire is added');
        $this->assertSame($questionnaire, $survey->getQuestionnaires()->first(), 'original questionnaire can be retreived from survey');
    }

    /**
     * @test
     */
    public function getJsonConfigForSurvey()
    {
        $survey = new Survey();
        $this->assertInternalType('array', $survey->getJsonConfig());
    }

    /**
     * @test
     */
    public function getJsonConfigReturnsSpecificFieldsForSurvey()
    {
        $survey = new Survey();
        $fields = array(
            'name',
            'code',
            'isActive',
            'year',
            'dateStart',
            'dateEnd',
        );
        $actual = $survey->getJsonConfig();
        foreach ($fields as $field) {
            $this->assertContains($field, $actual);
        }
    }

}
