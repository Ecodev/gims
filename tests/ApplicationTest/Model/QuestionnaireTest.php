<?php

namespace ApplicationTest\Model;

class QuestionaireTest extends AbstractModel
{

    public function testQuestionnaireComputingIsCorrect()
    {
        $cat1 = new \Application\Model\Category('cat 1');
        $cat11 = new \Application\Model\Category('cat 1.1 (sum of 1.*.1)');
        $cat12 = new \Application\Model\Category('cat 1.2 (sum of 1.*.2)');
        $cat13 = new \Application\Model\Category('cat 1.3');
        $cat131 = new \Application\Model\Category('cat 1.3.1');
        $cat132 = new \Application\Model\Category('cat 1.3.2');
        $cat14 = new \Application\Model\Category('cat 1.4');
        $cat141 = new \Application\Model\Category('cat 1.4.1');
        $cat142 = new \Application\Model\Category('cat 1.4.2');
        $cat2 = new \Application\Model\Category('cat 2');
        $cat21 = new \Application\Model\Category('cat 2.1');
        $cat3 = new \Application\Model\Category('cat 3 (sum of 2.* but with children as default to)');
        $cat31 = new \Application\Model\Category('cat 3.1');
        $cat32 = new \Application\Model\Category('cat 3.2');

        // Define tree structure
        $cat11->setParent($cat1);
        $cat12->setParent($cat1);
        $cat13->setParent($cat1);
        $cat14->setParent($cat1);
        $cat131->setParent($cat13);
        $cat132->setParent($cat13);
        $cat141->setParent($cat14);
        $cat142->setParent($cat14);
        $cat21->setParent($cat2);
        $cat31->setParent($cat3);
        $cat32->setParent($cat3);

        // Define categories with summands
        $cat11->addSummand($cat131)->addSummand($cat141);
        $cat12->addSummand($cat132)->addSummand($cat142);
        $cat3->addSummand($cat21);

        // Define questionnaire with answers for leaf categories only
        $questionnaire = new \Application\Model\Questionnaire();
        $question131 = new \Application\Model\Question();
        $question132 = new \Application\Model\Question();
        $question141 = new \Application\Model\Question();
        $question142 = new \Application\Model\Question();
        $question31 = new \Application\Model\Question();
        $question32 = new \Application\Model\Question();

        $question131->setCategory($cat131);
        $question132->setCategory($cat132);
        $question141->setCategory($cat141);
        $question142->setCategory($cat142);
        $question31->setCategory($cat31);
        $question32->setCategory($cat32);

        $answer131 = new \Application\Model\Answer();
        $answer132 = new \Application\Model\Answer();
        $answer141 = new \Application\Model\Answer();
        $answer142 = new \Application\Model\Answer();
        $answer31 = new \Application\Model\Answer();
        $answer32 = new \Application\Model\Answer();

        $answer131->setQuestionnaire($questionnaire)->setQuestion($question131)->setValueAbsolute(0.1);
        $answer132->setQuestionnaire($questionnaire)->setQuestion($question132)->setValueAbsolute(0.01);
        $answer141->setQuestionnaire($questionnaire)->setQuestion($question141)->setValueAbsolute(0.001);
        $answer142->setQuestionnaire($questionnaire)->setQuestion($question142)->setValueAbsolute(0.0001);
        $answer31->setQuestionnaire($questionnaire)->setQuestion($question31)->setValueAbsolute(0.00001);
        $answer32->setQuestionnaire($questionnaire)->setQuestion($question32)->setValueAbsolute(0.000001);

        // Assert computing for every single category
        $this->assertEquals($answer131->getValueAbsolute() + $answer132->getValueAbsolute() + $answer141->getValueAbsolute() + $answer142->getValueAbsolute(), $questionnaire->compute($cat1), 'should be the sum of unique children (excluding duplicates via summands)');
        $this->assertEquals($answer131->getValueAbsolute() + $answer141->getValueAbsolute(), $questionnaire->compute($cat11), 'should be the sum of summands');
        $this->assertEquals($answer132->getValueAbsolute() + $answer142->getValueAbsolute(), $questionnaire->compute($cat12), 'should be the sum of summands');
        $this->assertEquals($answer131->getValueAbsolute() + $answer132->getValueAbsolute(), $questionnaire->compute($cat13), 'should be the sum of children');
        $this->assertEquals($answer131->getValueAbsolute(), $questionnaire->compute($cat131), 'should be the answer, when answer specified');
        $this->assertEquals($answer132->getValueAbsolute(), $questionnaire->compute($cat132), 'should be the answer, when answer specified');
        $this->assertEquals($answer141->getValueAbsolute() + $answer142->getValueAbsolute(), $questionnaire->compute($cat14), 'should be the sum of children');
        $this->assertEquals($answer141->getValueAbsolute(), $questionnaire->compute($cat141), 'should be the answer, when answer specified');
        $this->assertEquals($answer142->getValueAbsolute(), $questionnaire->compute($cat142), 'should be the answer, when answer specified');
        $this->assertNull($questionnaire->compute($cat2), 'should be null, when no answer at all');
        $this->assertNull($questionnaire->compute($cat21), 'should be null, when no answer at all');
        $this->assertEquals($answer31->getValueAbsolute() + $answer32->getValueAbsolute(), $questionnaire->compute($cat3), 'should be the sum of children, when summands have no answer');
        $this->assertEquals($answer31->getValueAbsolute(), $questionnaire->compute($cat31), 'should be the answer, when answer specified');
        $this->assertEquals($answer32->getValueAbsolute(), $questionnaire->compute($cat32), 'should be the answer, when answer specified');


        // Overwrite computed categories with an answer
        $question11 = new \Application\Model\Question();
        $question13 = new \Application\Model\Question();
        $question11->setCategory($cat11);
        $question13->setCategory($cat13);
        $answer11 = new \Application\Model\Answer();
        $answer13 = new \Application\Model\Answer();
        $answer11->setQuestionnaire($questionnaire)->setQuestion($question11)->setValueAbsolute(0.0000001);
        $answer13->setQuestionnaire($questionnaire)->setQuestion($question13)->setValueAbsolute(0.00000001);

        // Assert that manually specified answer override computed values
        $this->assertEquals($answer11->getValueAbsolute(), $questionnaire->compute($cat11), 'should be the answer, when answer specified');
        $this->assertEquals($answer13->getValueAbsolute(), $questionnaire->compute($cat13), 'should be the answer, when answer specified');
        $this->assertEquals($answer11->getValueAbsolute() + $answer13->getValueAbsolute() + $answer132->getValueAbsolute() + $answer141->getValueAbsolute() + $answer142->getValueAbsolute(), $questionnaire->compute($cat1), 'should be the sum of children, but with overriden values instead of computed');

        // Add part to existing answer
        $part = new \Application\Model\Part('custom');
        $answer142->setPart($part);

        // Assert that we take part into consideration for filering answers
        $this->assertEquals($answer141->getValueAbsolute(), $questionnaire->compute($cat14), 'should be the sum of children, but only for selected part');
        $this->assertEquals($answer142->getValueAbsolute(), $questionnaire->compute($cat14, $part), 'should be the sum of children, but only for selected part');


        // Add alternative (non-official) category to previously unexisting answer
        $cat21bis = new \Application\Model\Category('cat 2.1 bis');
        $cat21bis->setOfficialCategory($cat21);
        $question21bis = new \Application\Model\Question();
        $question21bis->setCategory($cat21bis);
        $answer21bis = new \Application\Model\Answer();
        $answer21bis->setQuestionnaire($questionnaire)->setQuestion($question21bis)->setValueAbsolute(0.000000001);

        // Assert that alternative category is used for computation
        $this->assertEquals($answer21bis->getValueAbsolute(), $questionnaire->compute($cat2), 'should be the sum of children, including the answer which is specified with alternative category');
        $this->assertEquals($answer21bis->getValueAbsolute(), $questionnaire->compute($cat21), 'should be the alternative answer, when answer is specified with alternative category');
        $this->assertEquals($answer21bis->getValueAbsolute(), $questionnaire->compute($cat3), 'should be the sum of summands, when summands have answer');


        // Define summands to use several time cat1.4.1 (once via cat1 and once via cat1.4)
        $cat3->addSummand($cat1)->addSummand($cat14);
        $this->assertEquals($answer21bis->getValueAbsolute() + $answer11->getValueAbsolute() + $answer13->getValueAbsolute() + $answer132->getValueAbsolute() + $answer141->getValueAbsolute(), $questionnaire->compute($cat3), 'should not sum twice the same category');
    }

}
