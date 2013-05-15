<?php

namespace ApplicationTest\Model;

class SurveyTest extends AbstractModel
{

    public function testQuestionsReatlion()
    {
        $survey = new \Application\Model\Survey();
        $question = new \Application\Model\Question();


        $this->assertCount(0, $survey->getQuestions(), 'collection is initialized on creation');

        $question->setSurvey($survey);
        $this->assertCount(1, $survey->getQuestions(), 'survey must be notified when question is added');
        $this->assertSame($question, $survey->getQuestions()->first(), 'original question can be retreived from survey');
    }

}
