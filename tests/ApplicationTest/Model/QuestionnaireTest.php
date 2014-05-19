<?php

namespace ApplicationTest\Model;

use Application\Model\Questionnaire;

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

}
