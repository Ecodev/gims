<?php

namespace ApplicationTest\Model\Question;

use Application\Model\Question\Chapter;
use Application\Model\Question\NumericQuestion;

class ChapterTest extends AbstractModel
{

    public function testQuestionsRelation()
    {
        $chapter = new Chapter();
        $question = new NumericQuestion();

        $this->assertCount(0, $chapter->getQuestions(), 'collection is initialized on creation');

        $question->setChapter($chapter);
        $this->assertCount(1, $chapter->getQuestions(), 'chapter must be notified when question is added');
        $this->assertSame($question, $chapter->getQuestions()->first(), 'original question can be retreived from chapter');
    }

}
