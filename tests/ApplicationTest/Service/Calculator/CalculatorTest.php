<?php

namespace ApplicationTest\Service\Calculator;

use Application\Service\Calculator\Calculator;

class CalculatorTest extends AbstractCalculator
{

    /**
     * In those test we use a new calculator each time to avoid cache, because we will chagne filter structure on the fly
     */
    public function testComputingQuestionnaireIsCorrect()
    {
        // Assert computing for every single filter
        $this->assertEquals($this->answer131->getValueAbsolute() + $this->answer132->getValueAbsolute() + $this->answer141->getValueAbsolute() + $this->answer142->getValueAbsolute(), (new Calculator())->computeFilter($this->filter1, $this->questionnaire), 'should be the sum of unique children (excluding duplicates via summands)');
        $this->assertEquals($this->answer131->getValueAbsolute() + $this->answer141->getValueAbsolute(), (new Calculator())->computeFilter($this->filter11, $this->questionnaire), 'should be the sum of summands');
        $this->assertEquals($this->answer132->getValueAbsolute() + $this->answer142->getValueAbsolute(), (new Calculator())->computeFilter($this->filter12, $this->questionnaire), 'should be the sum of summands');
        $this->assertEquals($this->answer131->getValueAbsolute() + $this->answer132->getValueAbsolute(), (new Calculator())->computeFilter($this->filter13, $this->questionnaire), 'should be the sum of children');
        $this->assertEquals($this->answer131->getValueAbsolute(), (new Calculator())->computeFilter($this->filter131, $this->questionnaire), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer132->getValueAbsolute(), (new Calculator())->computeFilter($this->filter132, $this->questionnaire), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer141->getValueAbsolute() + $this->answer142->getValueAbsolute(), (new Calculator())->computeFilter($this->filter14, $this->questionnaire), 'should be the sum of children');
        $this->assertEquals($this->answer141->getValueAbsolute(), (new Calculator())->computeFilter($this->filter141, $this->questionnaire), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer142->getValueAbsolute(), (new Calculator())->computeFilter($this->filter142, $this->questionnaire), 'should be the answer, when answer specified');
        $this->assertNull((new Calculator())->computeFilter($this->filter2, $this->questionnaire), 'should be null, when no answer at all');
        $this->assertNull((new Calculator())->computeFilter($this->filter21, $this->questionnaire), 'should be null, when no answer at all');
        $this->assertEquals($this->answer31->getValueAbsolute() + $this->answer32->getValueAbsolute(), (new Calculator())->computeFilter($this->filter3, $this->questionnaire), 'should be the sum of children, when summands have no answer');
        $this->assertEquals($this->answer31->getValueAbsolute(), (new Calculator())->computeFilter($this->filter31, $this->questionnaire), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer32->getValueAbsolute(), (new Calculator())->computeFilter($this->filter32, $this->questionnaire), 'should be the answer, when answer specified');


        // Overwrite computed filters with an answer
        $this->question11 = new \Application\Model\Question();
        $this->question13 = new \Application\Model\Question();
        $this->question11->setFilter($this->filter11);
        $this->question13->setFilter($this->filter13);
        $this->answer11 = new \Application\Model\Answer();
        $this->answer13 = new \Application\Model\Answer();
        $this->answer11->setQuestionnaire($this->questionnaire)->setQuestion($this->question11)->setValueAbsolute(0.0000001);
        $this->answer13->setQuestionnaire($this->questionnaire)->setQuestion($this->question13)->setValueAbsolute(0.00000001);

        // Assert that manually specified answer override computed values
        $this->assertEquals($this->answer11->getValueAbsolute(), (new Calculator())->computeFilter($this->filter11, $this->questionnaire), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer131->getValueAbsolute() + $this->answer132->getValueAbsolute() + $this->answer13->getValueAbsolute(), (new Calculator())->computeFilter($this->filter13, $this->questionnaire), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer11->getValueAbsolute() + $this->answer13->getValueAbsolute() + $this->answer131->getValueAbsolute() + $this->answer132->getValueAbsolute() + $this->answer141->getValueAbsolute() + $this->answer142->getValueAbsolute(), (new Calculator())->computeFilter($this->filter1, $this->questionnaire), 'should be the sum of children, but with overriden values instead of computed');

        // Add part to existing answer
        $part = new \Application\Model\Part('custom');
        $this->answer142->setPart($part);

        // Assert that we take part into consideration for filering answers
        $this->assertEquals($this->answer141->getValueAbsolute(), (new Calculator())->computeFilter($this->filter14, $this->questionnaire), 'should be the sum of children, but only for selected part');
        $this->assertEquals($this->answer142->getValueAbsolute(), (new Calculator())->computeFilter($this->filter14, $this->questionnaire, $part), 'should be the sum of children, but only for selected part');


        // Add alternative (non-official) filter to previously unexisting answer
        $this->filter21bis = new \Application\Model\Filter('cat 2.1 bis');
        $this->filter21bis->setOfficialFilter($this->filter21);
        $this->question21bis = new \Application\Model\Question();
        $this->question21bis->setFilter($this->filter21bis);
        $this->answer21bis = new \Application\Model\Answer();
        $this->answer21bis->setQuestionnaire($this->questionnaire)->setQuestion($this->question21bis)->setValueAbsolute(0.000000001);

        // Assert that alternative filter is used for computation
        $this->assertEquals($this->answer21bis->getValueAbsolute(), (new Calculator())->computeFilter($this->filter2, $this->questionnaire), 'should be the sum of children, including the answer which is specified with alternative filter');
        $this->assertEquals($this->answer21bis->getValueAbsolute(), (new Calculator())->computeFilter($this->filter21, $this->questionnaire), 'should be the alternative answer, when answer is specified with alternative filter');
        $this->assertEquals($this->answer21bis->getValueAbsolute(), (new Calculator())->computeFilter($this->filter3, $this->questionnaire), 'should be the sum of summands, when summands have answer');


        // Define summands to use several time cat1.4.1 (once via cat1 and once via cat1.4)
        $this->filter3->addSummand($this->filter1)->addSummand($this->filter14);
        $this->assertEquals($this->answer21bis->getValueAbsolute() + $this->answer11->getValueAbsolute() + $this->answer13->getValueAbsolute() + $this->answer131->getValueAbsolute() + $this->answer132->getValueAbsolute() + $this->answer141->getValueAbsolute(), (new Calculator())->computeFilter($this->filter3, $this->questionnaire), 'should not sum twice the same filter');
    }

    public function testComputingFilterIsCorrect()
    {
        $service = new \Application\Service\Calculator\Calculator();

        $this->assertNull($service->computeFilter(new \Application\Model\Filter(), $this->questionnaire), 'empty filter result is always null');
        $this->assertEquals(0.1111, $service->computeFilter($this->highFilter1, $this->questionnaire), 'when only one filter should be equal to that filter');
        $this->assertNull($service->computeFilter($this->highFilter2, $this->questionnaire), 'when only one filter is null should also be null');
        $this->assertEquals(0.111111, $service->computeFilter($this->highFilter3, $this->questionnaire), 'sum all filters');
    }

    public function testCacheOnFilter()
    {
        $service = new \Application\Service\Calculator\Calculator();

        $res1 = $service->computeFilter($this->highFilter3, $this->questionnaire);
        $this->answer131->setValueAbsolute((12345));
        $res2 = $service->computeFilter($this->highFilter3, $this->questionnaire);
        $this->assertEquals($res1, $res2, 'result should be cached and therefore be the same');

        $service2 = new \Application\Service\Calculator\Calculator();
        $res3 = $service2->computeFilter($this->highFilter3, $this->questionnaire);
        $this->assertNotEquals($res1, $res3, 'after clearing cache, result differs');
        $this->assertEquals(12345.011111, $res3, 'after clearing cache, result reflect new values');
    }

    public function testCumulatedFormulasAreSummed()
    {
        $service = new \Application\Service\Calculator\Calculator();

        // Define filter1 to add 0.5
        $formula = new \Application\Model\Rule\Formula();
        $formula->setValue(0.5);
        $filterRule = new \Application\Model\Rule\FilterRule();
        $filterRule->setFilter($this->filter1)->setQuestionnaire($this->questionnaire)->setRule($formula);

        // Define filter1 to also add 0.1
        $formula2 = new \Application\Model\Rule\Formula();
        $formula2->setValue(0.1);
        $filterRule2 = new \Application\Model\Rule\FilterRule();
        $filterRule2->setFilter($this->filter1)->setQuestionnaire($this->questionnaire)->setRule($formula2);

        $this->assertEquals(0.7111, $service->computeFilter($this->filter1, $this->questionnaire));
    }

}
