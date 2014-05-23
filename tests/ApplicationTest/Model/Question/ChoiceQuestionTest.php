<?php

namespace ApplicationTest\Model\Question;

use Application\Model\Question\ChoiceQuestion;
use Application\Model\Question\Choice;

class ChoiceQuestionTest extends \ApplicationTest\Model\AbstractModel
{

    public function testChoicesRelation()
    {
        $question = new ChoiceQuestion();
        $choice = new Choice();

        $this->assertCount(0, $question->getChoices(), 'collection is initialized on creation');

        $choice->setQuestion($question);
        $this->assertCount(1, $question->getChoices(), 'question must be notified when choice is added');
        $this->assertSame($choice, $question->getChoices()->first(), 'original choice can be retreived from question');
    }

    public function testChoicesCanBeSet()
    {
        $choices = new \Doctrine\Common\Collections\ArrayCollection();
        $choices->add(new Choice());
        $choices->add(new Choice());
        $choices->add(new Choice());
        $question = new ChoiceQuestion();

        $this->assertCount(0, $question->getChoices(), 'collection is initialized on creation');

        $question->setChoices($choices);
        $this->assertCount(3, $question->getChoices(), 'question must be notified when choice is added');
        $this->assertNotSame($choices, $question->getChoices(), 'collection is not the same...');
        $this->assertEquals($choices, $question->getChoices(), '... but their content is the same');
    }

    public function testChoicesAreUnique()
    {
        $choices = new \Doctrine\Common\Collections\ArrayCollection();
        $duplicatedChoice = new Choice();
        $choices->add($duplicatedChoice);
        $choices->add($duplicatedChoice);
        $question = new ChoiceQuestion();

        $question->setChoices($choices);
        $this->assertCount(1, $question->getChoices(), 'question must be notified when choice is added');
    }

    public function testChoicesAlreadyExistingAreKept()
    {
        $question = new ChoiceQuestion();
        $choices1 = new \Doctrine\Common\Collections\ArrayCollection();
        $choices2 = new \Doctrine\Common\Collections\ArrayCollection();
        $choice1 = new Choice();
        $alreadyExistingChoice = new Choice();
        $choice2 = new Choice();

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
