<?php

namespace ApplicationTest\Model;

use Application\Model\Survey;
use Application\Model\Question\NumericQuestion;

/**
 * @group Model
 */
class SurveyTest extends AbstractModel
{

    public function testQuestionsRelation()
    {
        $survey = new Survey();
        $question = new NumericQuestion();

        $this->assertCount(0, $survey->getQuestions(), 'collection is initialized on creation');

        $question->setSurvey($survey);
        $this->assertCount(1, $survey->getQuestions(), 'survey must be notified when question is added');
        $this->assertSame($question, $survey->getQuestions()->first(), 'original question can be retrieved from survey');
    }

    public function testSurveyType()
    {
        $survey = new Survey();
        $this->assertEquals(\Application\Model\SurveyType::$GLAAS, $survey->getType(), 'survey should default to GLAAS type');

        $question = new NumericQuestion();
        $question->setIsPopulation(true);
        $question->setIsAbsolute(false);
        $question->setSurvey($survey);
        $this->assertEquals(\Application\Model\SurveyType::$JMP, $survey->getType(), 'survey should automatically switch to JMP');

        $chapter = new \Application\Model\Question\Chapter();
        $chapter->setSurvey($survey);
        $this->assertEquals(\Application\Model\SurveyType::$GLAAS, $survey->getType(), 'survey should automatically switch back to GLAAS');
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

    public function testYearNullable()
    {
        $survey = new Survey();
        $this->assertSame(null, $survey->getYear(), 'should be NULL by default');

        $survey->setYear('1991');
        $this->assertNotSame('1991', $survey->getYear(), 'should never return string');
        $this->assertSame(1991, $survey->getYear(), 'should always return integer');

        $survey->setYear(null);
        $this->assertSame(null, $survey->getYear(), 'should be able to re-set NULL');
    }

}
