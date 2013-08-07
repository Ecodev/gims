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

    public function testQuestionChoicesAreUnique()
    {
        $choices = new \Doctrine\Common\Collections\ArrayCollection();
        $duplicatedChoice = new QuestionChoice();
        $choices->add($duplicatedChoice);
        $choices->add($duplicatedChoice);
        $question = new Question();

        $question->setChoices($choices);
        $this->assertCount(1, $question->getChoices(), 'question must be notified when choice is added');
    }

    public function testQuestionChoicesAlreadyExistingAreKept()
    {
        $question = new Question();
        $choices1 = new \Doctrine\Common\Collections\ArrayCollection();
        $choices2 = new \Doctrine\Common\Collections\ArrayCollection();
        $choice1 = new QuestionChoice();
        $alreadyExistingChoice = new QuestionChoice();
        $choice2 = new QuestionChoice();

        $choices1->add($choice1);
        $choices1->add($alreadyExistingChoice);

        $choices2->add($choice2);
        $choices2->add($alreadyExistingChoice);

        $question->setChoices($choices1);
        $this->assertCount(2, $question->getChoices());

        $question->setChoices($choices2);
        $this->assertCount(2, $question->getChoices());
        $this->assertFalse($question->getChoices()->contains($choice1), 'non-common question choice must be removed');
        $this->assertTrue($question->getChoices()->contains($alreadyExistingChoice), 'common question choice must be kept');
        $this->assertTrue($question->getChoices()->contains($choice2), 'new question choice must be added');
    }

}
