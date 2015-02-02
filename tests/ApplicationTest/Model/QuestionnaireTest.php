<?php

namespace ApplicationTest\Model;

use Application\Model\Questionnaire;

/**
 * @group Model
 */
class QuestionnaireTest extends AbstractModel
{

    public function commentsDataProvider()
    {
        return [
            ['', null, ''],
            ['', '', ''],
            ['original', null, 'original'],
            ['original', '', 'original'],
            ['original', '   ', 'original'],
            ['original', 'append', 'original' . PHP_EOL . PHP_EOL . 'append'],
            ['', 'append', 'append'],
            ['', '  append  ', 'append'],
        ];
    }

    /**
     * @dataProvider commentsDataProvider
     */
    public function testComments($original, $toAppend, $expected)
    {
        $questionnaire = new Questionnaire();
        $this->assertSame('', $questionnaire->getComments(), 'new Questionnaire should have empty string');

        $questionnaire->setComments($original);
        $this->assertSame($original, $questionnaire->getComments(), 'should be able to get comments that we set');

        $questionnaire->appendComment($toAppend);
        $this->assertSame($expected, $questionnaire->getComments(), 'should be able to get comments that we appended');
    }

    public function testRelations()
    {
        $questionnaire = new \Application\Model\Questionnaire();

        // Test Geoname
        $geoname = new \Application\Model\Geoname();
        $this->assertCount(0, $geoname->getQuestionnaires(), 'collection is initialized on creation');
        $questionnaire->setGeoname($geoname);
        $this->assertCount(1, $geoname->getQuestionnaires(), 'geoname must be notified when questionnaire is added');
        $this->assertSame($questionnaire, $geoname->getQuestionnaires()->first(), 'original questionnaire can be retrieved from geoname');

        // Test Survey
        $survey = new \Application\Model\Survey();
        $this->assertCount(0, $survey->getQuestionnaires(), 'collection is initialized on creation');
        $questionnaire->setSurvey($survey);
        $this->assertCount(1, $survey->getQuestionnaires(), 'survey must be notified when questionnaire is added');
        $this->assertSame($questionnaire, $survey->getQuestionnaires()->first(), 'original questionnaire can be retrieved from survey');
    }
}
