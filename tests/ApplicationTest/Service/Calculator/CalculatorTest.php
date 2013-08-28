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
        $this->assertEquals($this->answer131->getValueAbsolute() + $this->answer132->getValueAbsolute() + $this->answer141->getValueAbsolute() + $this->answer142->getValueAbsolute(), (new Calculator())->computeFilter($this->filter1, $this->questionnaire, $this->part), 'should be the sum of unique children (excluding duplicates via summands)');
        $this->assertEquals($this->answer131->getValueAbsolute() + $this->answer141->getValueAbsolute(), (new Calculator())->computeFilter($this->filter11, $this->questionnaire, $this->part), 'should be the sum of summands');
        $this->assertEquals($this->answer132->getValueAbsolute() + $this->answer142->getValueAbsolute(), (new Calculator())->computeFilter($this->filter12, $this->questionnaire, $this->part), 'should be the sum of summands');
        $this->assertEquals($this->answer131->getValueAbsolute() + $this->answer132->getValueAbsolute(), (new Calculator())->computeFilter($this->filter13, $this->questionnaire, $this->part), 'should be the sum of children');
        $this->assertEquals($this->answer131->getValueAbsolute(), (new Calculator())->computeFilter($this->filter131, $this->questionnaire, $this->part), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer132->getValueAbsolute(), (new Calculator())->computeFilter($this->filter132, $this->questionnaire, $this->part), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer141->getValueAbsolute() + $this->answer142->getValueAbsolute(), (new Calculator())->computeFilter($this->filter14, $this->questionnaire, $this->part), 'should be the sum of children');
        $this->assertEquals($this->answer141->getValueAbsolute(), (new Calculator())->computeFilter($this->filter141, $this->questionnaire, $this->part), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer142->getValueAbsolute(), (new Calculator())->computeFilter($this->filter142, $this->questionnaire, $this->part), 'should be the answer, when answer specified');
        $this->assertNull((new Calculator())->computeFilter($this->filter2, $this->questionnaire, $this->part), 'should be null, when no answer at all');
        $this->assertNull((new Calculator())->computeFilter($this->filter21, $this->questionnaire, $this->part), 'should be null, when no answer at all');
        $this->assertEquals($this->answer31->getValueAbsolute() + $this->answer32->getValueAbsolute(), (new Calculator())->computeFilter($this->filter3, $this->questionnaire, $this->part), 'should be the sum of children, when summands have no answer');
        $this->assertEquals($this->answer31->getValueAbsolute(), (new Calculator())->computeFilter($this->filter31, $this->questionnaire, $this->part), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer32->getValueAbsolute(), (new Calculator())->computeFilter($this->filter32, $this->questionnaire, $this->part), 'should be the answer, when answer specified');


        // Overwrite computed filters with an answer
        $this->question11 = new \Application\Model\Question\NumericQuestion();
        $this->question13 = new \Application\Model\Question\NumericQuestion();
        $this->question11->setFilter($this->filter11);
        $this->question13->setFilter($this->filter13);
        $this->answer11 = new \Application\Model\Answer();
        $this->answer13 = new \Application\Model\Answer();
        $this->answer11->setPart($this->part)->setQuestionnaire($this->questionnaire)->setQuestion($this->question11)->setValueAbsolute(0.0000001);
        $this->answer13->setPart($this->part)->setQuestionnaire($this->questionnaire)->setQuestion($this->question13)->setValueAbsolute(0.00000001);

        // Assert that manually specified answer override computed values
        $this->assertEquals($this->answer11->getValueAbsolute(), (new Calculator())->computeFilter($this->filter11, $this->questionnaire, $this->part), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer131->getValueAbsolute() + $this->answer132->getValueAbsolute() + $this->answer13->getValueAbsolute(), (new Calculator())->computeFilter($this->filter13, $this->questionnaire, $this->part), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer11->getValueAbsolute() + $this->answer13->getValueAbsolute() + $this->answer131->getValueAbsolute() + $this->answer132->getValueAbsolute() + $this->answer141->getValueAbsolute() + $this->answer142->getValueAbsolute(), (new Calculator())->computeFilter($this->filter1, $this->questionnaire, $this->part), 'should be the sum of children, but with overriden values instead of computed');

        // Add part to existing answer
        $part = new \Application\Model\Part('custom');
        $this->answer142->setPart($part);

        // Assert that we take part into consideration for filering answers
        $this->assertEquals($this->answer141->getValueAbsolute(), (new Calculator())->computeFilter($this->filter14, $this->questionnaire, $this->part), 'should be the sum of children, but only for selected part');
        $this->assertEquals($this->answer142->getValueAbsolute(), (new Calculator())->computeFilter($this->filter14, $this->questionnaire, $part), 'should be the sum of children, but only for selected part');


        // Add alternative (non-official) filter to previously unexisting answer
        $this->filter21bis = new \Application\Model\Filter('cat 2.1 bis');
        $this->filter21bis->setOfficialFilter($this->filter21);
        $this->question21bis = new \Application\Model\Question\NumericQuestion();
        $this->question21bis->setFilter($this->filter21bis);
        $this->answer21bis = new \Application\Model\Answer();
        $this->answer21bis->setPart($this->part)->setQuestionnaire($this->questionnaire)->setQuestion($this->question21bis)->setValueAbsolute(0.000000001);

        // Assert that alternative filter is used for computation
        $this->assertEquals($this->answer21bis->getValueAbsolute(), (new Calculator())->computeFilter($this->filter2, $this->questionnaire, $this->part), 'should be the sum of children, including the answer which is specified with alternative filter');
        $this->assertEquals($this->answer21bis->getValueAbsolute(), (new Calculator())->computeFilter($this->filter21, $this->questionnaire, $this->part), 'should be the alternative answer, when answer is specified with alternative filter');
        $this->assertEquals($this->answer21bis->getValueAbsolute(), (new Calculator())->computeFilter($this->filter3, $this->questionnaire, $this->part), 'should be the sum of summands, when summands have answer');


        // Define summands to use several time cat1.4.1 (once via cat1 and once via cat1.4)
        $this->filter3->addSummand($this->filter1)->addSummand($this->filter14);
        $this->assertEquals($this->answer21bis->getValueAbsolute() + $this->answer11->getValueAbsolute() + $this->answer13->getValueAbsolute() + $this->answer131->getValueAbsolute() + $this->answer132->getValueAbsolute() + $this->answer141->getValueAbsolute(), (new Calculator())->computeFilter($this->filter3, $this->questionnaire, $this->part), 'should not sum twice the same filter');
    }

    public function testComputingFilterIsCorrect()
    {
        $service = new \Application\Service\Calculator\Calculator();

        $this->assertNull($service->computeFilter(new \Application\Model\Filter(), $this->questionnaire, $this->part), 'empty filter result is always null');
        $this->assertEquals(0.1111, $service->computeFilter($this->highFilter1, $this->questionnaire, $this->part), 'when only one filter should be equal to that filter');
        $this->assertNull($service->computeFilter($this->highFilter2, $this->questionnaire, $this->part), 'when only one filter is null should also be null');
        $this->assertEquals(0.111111, $service->computeFilter($this->highFilter3, $this->questionnaire, $this->part), 'sum all filters');
    }

    public function testCacheOnFilter()
    {
        $service = new \Application\Service\Calculator\Calculator();

        $res1 = $service->computeFilter($this->highFilter3, $this->questionnaire, $this->part);
        $this->answer131->setValueAbsolute((12345));
        $res2 = $service->computeFilter($this->highFilter3, $this->questionnaire, $this->part);
        $this->assertEquals($res1, $res2, 'result should be cached and therefore be the same');

        $service2 = new \Application\Service\Calculator\Calculator();
        $res3 = $service2->computeFilter($this->highFilter3, $this->questionnaire, $this->part);
        $this->assertNotEquals($res1, $res3, 'after clearing cache, result differs');
        $this->assertEquals(12345.011111, $res3, 'after clearing cache, result reflect new values');
    }

    public function testFormulaOverrideNormalResult()
    {
        $service = new \Application\Service\Calculator\Calculator();

        // Define filter1 to actually be the result of a formula
        $formula = new \Application\Model\Rule\Formula();
        $formula->setFormula('=0.5');
        $filterRule = new \Application\Model\Rule\FilterRule();
        $filterRule->setPart($this->part)->setFilter($this->filter1)->setQuestionnaire($this->questionnaire)->setRule($formula);

        $this->assertEquals(0.5, $service->computeFilter($this->filter1, $this->questionnaire, $this->part));
    }

    public function testFormulaSyntax()
    {
        $service = new \Application\Service\Calculator\Calculator();
        $service->setServiceLocator($this->getApplicationServiceLocator());

        // Create a stub for the FilterRepository class with predetermined values, so we don't have to mess with database
        $nullFilter = new \Application\Model\Filter();
        $stubFilterRepository = $this->getMock('\Application\Repository\FilterRepository', array('findOneById', 'findOneBy'), array(), '', false);
        $stubFilterRepository->expects($this->any())
                ->method('findOneById')
                ->will($this->returnValueMap(array(
                            array('1', $this->filter1),
                            array('11', $this->filter11),
                            array('12', $this->filter12),
                            array('666', $nullFilter),
        )));
        $service->setFilterRepository($stubFilterRepository);

        // Create a stub for the QuestionnaireRepository class with predetermined values, so we don't have to mess with database
        $stubQuestionnaireRepository = $this->getMock('\Application\Repository\QuestionnaireRepository', array('findOneById'), array(), '', false);
        $stubQuestionnaireRepository->expects($this->any())
                ->method('findOneById')
                ->will($this->returnValueMap(array(
                            array('34', $this->questionnaire),
        )));
        $service->setQuestionnaireRepository($stubQuestionnaireRepository);

        // Create a stub for the PartRepository class with predetermined values, so we don't have to mess with database
        $stubPartRepository = $this->getMock('\Application\Repository\PartRepository', array('findOneById'), array(), '', false);
        $stubPartRepository->expects($this->any())
                ->method('findOneById')
                ->will($this->returnValueMap(array(
                            array('56', $this->part),
        )));
        $service->setPartRepository($stubPartRepository);

        // Create a stub for the QuestionnaireFormulaRepository class with predetermined values, so we don't have to mess with database
        $questionnaireFormula = new \Application\Model\Rule\QuestionnaireFormula();
        $questionnaireFormula->setFormula((new \Application\Model\Rule\Formula())->setFormula('=2+3'))
                ->setQuestionnaire($this->questionnaire)
                ->setPart($this->part);
        $stubQuestionnaireFormulaRepository = $this->getMock('\Application\Repository\QuestionnaireFormulaRepository', array('findOneBy'), array(), '', false);
        $stubQuestionnaireFormulaRepository->expects($this->any())
                ->method('findOneBy')
                ->will($this->returnValue($questionnaireFormula)
        );
        $service->setQuestionnaireFormulaRepository($stubQuestionnaireFormulaRepository);
        $this->assertEquals($questionnaireFormula, $stubQuestionnaireFormulaRepository->findOneBy(array('foo')));

        $formula = new \Application\Model\Rule\Formula();

        $formula->setFormula('=(3 + 7) * SUM(2, 3)');
        $this->assertEquals(50, $service->computeFormula($formula, $this->questionnaire, $this->part), 'should be able to handle standard Excel things');


        // Filter values
        $formula->setFormula('= 10 + {F#11,Q#34,P#56}');
        $this->assertEquals(10.101, $service->computeFormula($formula, $this->questionnaire, $this->part), 'should be able to refer a Filter value');

        $formula->setFormula('= 10 + {F#current,Q#current,P#current}');
        $this->assertEquals(10.101, $service->computeFormula($formula, $this->questionnaire, $this->part, $this->filter11), 'should be able to refer a Filter value with same Questionnaire and Part');

        $formula->setFormula('= 10 + {F#666,Q#34,P#56}');
        $this->assertEquals(10, $service->computeFormula($formula, $this->questionnaire, $this->part), 'should be able to refer a Filter value which is NULL');


        // QuestionnaireFormula values
        $formula->setFormula('={Fo#12,Q#34,P#56}');
        $this->assertEquals(5, $service->computeFormula($formula, $this->questionnaire, $this->part), 'should be able to refer a QuestionnaireFormula');

        $formula->setFormula('={Fo#12,Q#current,P#current}');
        $this->assertEquals(5, $service->computeFormula($formula, $this->questionnaire, $this->part), 'should be able to refer a QuestionnaireFormula with same Questionnaire and Part');

        $questionnaireFormula->getFormula()->setFormula('=NULL');
        $formula->setFormula('=7 + {Fo#12,Q#current,P#current}');
        $this->assertEquals(7, $service->computeFormula($formula, $this->questionnaire, $this->part), 'should be able to refer a QuestionnaireFormula which is NULL');


        // Unnoficial filter names
        $formula->setFormula('={F#12,Q#34}');
        $this->assertNull($service->computeFormula($formula, $this->questionnaire, $this->part), 'refering a non-existing Unofficial Filter name, returns null');

        // Inject our unnofficial filter
        $unofficialFilter = new \Application\Model\Filter('unofficial with "double quotes"');
        $unofficialFilter->setQuestionnaire($this->questionnaire)->setOfficialFilter($this->filter1);
        $stubFilterRepository->expects($this->any())
                ->method('findOneBy')
                ->will($this->returnValue($unofficialFilter));

        $formula->setFormula('=ISTEXT({F#12,Q#34})');
        $this->assertTrue($service->computeFormula($formula, $this->questionnaire, $this->part), 'should be able to refer an Unofficial Filter name');
    }

    public function testFormulaSyntaxSelf()
    {
        $formula = new \Application\Model\Rule\Formula();

        $fitlerRule = new \Application\Model\Rule\FilterRule();
        $fitlerRule->setRule($formula)
                ->setFilter($this->filter1)
                ->setQuestionnaire($this->questionnaire)
                ->setPart($this->part);

        $formula->setFormula('={self}');
        $this->assertEquals(0.1111, (new Calculator())->computeFormula($formula, $this->questionnaire, $this->part, $this->filter1), 'should fallback to filter value without any formulas');

        // Same result as above, but with different use of our API
        $this->assertEquals(0.1111, (new Calculator())->computeFilter($this->filter1, $this->questionnaire, $this->part), 'should fallback to filter value without any formulas');

        // We now add a second formula, that will be used as a fallback from first one
        $formula2 = new \Application\Model\Rule\Formula();
        $formula2->setFormula('=8 * 2');

        $fitlerRule2 = new \Application\Model\Rule\FilterRule();
        $fitlerRule2->setRule($formula2)
                ->setFilter($this->filter1)
                ->setQuestionnaire($this->questionnaire)
                ->setPart($this->part);

        $this->assertEquals(16, (new Calculator())->computeFilter($this->filter1, $this->questionnaire, $this->part), 'should fallback to second formula');
    }

}
