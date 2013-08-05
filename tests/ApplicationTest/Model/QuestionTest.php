<?php

namespace ApplicationTest\Model;

use Application\Model\Question;
use Application\Model\QuestionChoice;

class QuestionTest extends AbstractModel
{

    public function testQuestionChoicesRelation()
    {
        $question = new Question();
        $choice = new QuestionChoice();


        $this->assertCount(0, $question->getChoices(), 'collection is initialized on creation');

        $choice->setQuestion($question);
        $this->assertCount(1, $question->getChoices(), 'question must be notified when choice is added');
        $this->assertSame($choice, $question->getChoices()->first(), 'original choice can be retreived from question');
    }

    public function testQuestionChoicesCanBeSet()
    {
        $choices = new \Doctrine\Common\Collections\ArrayCollection();
        $choices->add(new QuestionChoice());
        $choices->add(new QuestionChoice());
        $choices->add(new QuestionChoice());
        $question = new Question();

        $this->assertCount(0, $question->getChoices(), 'collection is initialized on creation');

        $question->setChoices($choices);
        $this->assertCount(3, $question->getChoices(), 'question must be notified when choice is added');
        $this->assertNotSame($choices, $question->getChoices(), 'collection is not the same...');
        $this->assertEquals($choices, $question->getChoices(), '... but their content is the same');
    }

}
