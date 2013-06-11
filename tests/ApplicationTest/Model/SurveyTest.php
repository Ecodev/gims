<?php

namespace ApplicationTest\Model;

use Application\Model\Question;
use Application\Model\Survey;

class SurveyTest extends AbstractModel
{

    public function testQuestionsReatlion()
    {
        $survey = new Survey();
        $question = new Question();


        $this->assertCount(0, $survey->getQuestions(), 'collection is initialized on creation');

        $question->setSurvey($survey);
        $this->assertCount(1, $survey->getQuestions(), 'survey must be notified when question is added');
        $this->assertSame($question, $survey->getQuestions()->first(), 'original question can be retreived from survey');
    }

    /**
     * @test
     */
    public function getJsonConfigForSurvey() {
        $this->assertInternalType('array', Survey::getJsonConfig());
    }

    /**
     * @test
     */
    public function getJsonConfigReturnsSpecificFieldsForSurvey() {
        $fields = array(
            'name',
            'code',
            'active',
            'year',
            'dateStart',
            'dateEnd',
        );
        $actual = Survey::getJsonConfig();
        foreach ($fields as $field) {
            $this->assertContains($field, $actual);
        }
    }

    /**
     * @test
     */
    public function getMetaDataOfSurveyReturnsAnArray() {
        $actual = Survey::getMetadata();
        $this->assertInternalType('array', $actual);
        $this->assertNotNull($actual);
    }

}
