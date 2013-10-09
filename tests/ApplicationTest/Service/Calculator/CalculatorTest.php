<?php

namespace ApplicationTest\Service\Calculator;

class CalculatorTest extends AbstractCalculator
{

    /**
     * In those test we use a new calculator each time to avoid cache, because we will chagne filter structure on the fly
     */
    public function testComputingQuestionnaireIsCorrect()
    {
        // Assert computing for every single filter
        $this->assertEquals($this->answer131->getValuePercent() + $this->answer132->getValuePercent() + $this->answer141->getValuePercent() + $this->answer142->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter1->getId(), $this->questionnaire, $this->part->getId()), 'should be the sum of unique children (excluding duplicates via summands)');
        $this->assertEquals($this->answer131->getValuePercent() + $this->answer141->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter11->getId(), $this->questionnaire, $this->part->getId()), 'should be the sum of summands');
        $this->assertEquals($this->answer132->getValuePercent() + $this->answer142->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter12->getId(), $this->questionnaire, $this->part->getId()), 'should be the sum of summands');
        $this->assertEquals($this->answer131->getValuePercent() + $this->answer132->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter13->getId(), $this->questionnaire, $this->part->getId()), 'should be the sum of children');
        $this->assertEquals($this->answer131->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter131->getId(), $this->questionnaire, $this->part->getId()), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer132->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter132->getId(), $this->questionnaire, $this->part->getId()), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer141->getValuePercent() + $this->answer142->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter14->getId(), $this->questionnaire, $this->part->getId()), 'should be the sum of children');
        $this->assertEquals($this->answer141->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter141->getId(), $this->questionnaire, $this->part->getId()), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer142->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter142->getId(), $this->questionnaire, $this->part->getId()), 'should be the answer, when answer specified');
        $this->assertNull($this->getNewCalculator()->computeFilter($this->filter2->getId(), $this->questionnaire, $this->part->getId()), 'should be null, when no answer at all');
        $this->assertNull($this->getNewCalculator()->computeFilter($this->filter21->getId(), $this->questionnaire, $this->part->getId()), 'should be null, when no answer at all');
        $this->assertEquals($this->answer31->getValuePercent() + $this->answer32->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter3->getId(), $this->questionnaire, $this->part->getId()), 'should be the sum of children, when summands have no answer');
        $this->assertEquals($this->answer31->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter31->getId(), $this->questionnaire, $this->part->getId()), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer32->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter32->getId(), $this->questionnaire, $this->part->getId()), 'should be the answer, when answer specified');

        // Overwrite computed filters with an answer
        $this->question11 = new \Application\Model\Question\NumericQuestion();
        $this->question13 = new \Application\Model\Question\NumericQuestion();
        $this->question11->setFilter($this->filter11);
        $this->question13->setFilter($this->filter13);
        $this->answer11 = new \Application\Model\Answer();
        $this->answer13 = new \Application\Model\Answer();
        $this->answer11->setPart($this->part)->setQuestionnaire($this->questionnaire)->setQuestion($this->question11)->setValuePercent(0.0000001);
        $this->answer13->setPart($this->part)->setQuestionnaire($this->questionnaire)->setQuestion($this->question13)->setValuePercent(0.00000001);

        // Assert that manually specified answer override computed values
        $this->assertEquals($this->answer11->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter11->getId(), $this->questionnaire, $this->part->getId()), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer13->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter13->getId(), $this->questionnaire, $this->part->getId()), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer11->getValuePercent() + $this->answer13->getValuePercent() + $this->answer132->getValuePercent() + $this->answer141->getValuePercent() + $this->answer142->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter1->getId(), $this->questionnaire, $this->part->getId()), 'should be the sum of children, but with overriden values instead of computed');

        // Add part to existing answer
        $part = $this->getNewModelWithId('\Application\Model\Part')->setName('custom');
        $this->answer142->setPart($part);

        // Assert that we take part into consideration for filering answers
        $this->assertEquals($this->answer141->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter14->getId(), $this->questionnaire, $this->part->getId()), 'should be the sum of children, but only for selected part (1)');
        $this->assertEquals($this->answer142->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter14->getId(), $this->questionnaire, $part->getId()), 'should be the sum of children, but only for selected part (2)');

        // Add alternative (non-official) filter to previously unexisting answer
        $this->filter21bis = $this->getNewModelWithId('\Application\Model\Filter')->setName('cat 2.1 bis');
        $this->filter21bis->setOfficialFilter($this->filter21);
        $this->question21bis = new \Application\Model\Question\NumericQuestion();
        $this->question21bis->setFilter($this->filter21bis);
        $this->answer21bis = new \Application\Model\Answer();
        $this->answer21bis->setPart($this->part)->setQuestionnaire($this->questionnaire)->setQuestion($this->question21bis)->setValuePercent(0.000000001);

        // Assert that alternative filter is used for computation
        $this->assertEquals($this->answer21bis->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter2->getId(), $this->questionnaire, $this->part->getId()), 'should be the sum of children, including the answer which is specified with alternative filter');
        $this->assertEquals($this->answer21bis->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter21->getId(), $this->questionnaire, $this->part->getId()), 'should be the alternative answer, when answer is specified with alternative filter');
        $this->assertEquals($this->answer21bis->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter3->getId(), $this->questionnaire, $this->part->getId()), 'should be the sum of summands, when summands have answer');

        // Define summands to use several time cat1.4.1 (once via cat1 and once via cat1.4)
        $this->filter3->addSummand($this->filter1)->addSummand($this->filter14);
        $this->assertEquals($this->answer21bis->getValuePercent() + $this->answer11->getValuePercent() + $this->answer13->getValuePercent() + $this->answer132->getValuePercent() + $this->answer141->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter3->getId(), $this->questionnaire, $this->part->getId()), 'should not sum twice the same filter');
    }

    public function testComputingFilterIsCorrect()
    {
        $service = $this->getNewCalculator();

        $this->assertNull($service->computeFilter($this->getNewModelWithId('\Application\Model\Filter')->getId(), $this->questionnaire, $this->part->getId()), 'empty filter result is always null');
        $this->assertEquals(0.1111, $service->computeFilter($this->highFilter1->getId(), $this->questionnaire, $this->part->getId()), 'when only one filter should be equal to that filter');
        $this->assertNull($service->computeFilter($this->highFilter2->getId(), $this->questionnaire, $this->part->getId()), 'when only one filter is null should also be null');
        $this->assertEquals(0.111111, $service->computeFilter($this->highFilter3->getId(), $this->questionnaire, $this->part->getId()), 'sum all filters');
    }

    public function testCacheOnFilter()
    {
        $service = $this->getNewCalculator();

        $res1 = $service->computeFilter($this->highFilter3->getId(), $this->questionnaire, $this->part->getId());
        $this->answer131->setValuePercent((12345));
        $res2 = $service->computeFilter($this->highFilter3->getId(), $this->questionnaire, $this->part->getId());
        $this->assertEquals($res1, $res2, 'result should be cached and therefore be the same');

        $service2 = $this->getNewCalculator();
        $res3 = $service2->computeFilter($this->highFilter3->getId(), $this->questionnaire, $this->part->getId());
        $this->assertNotEquals($res1, $res3, 'after clearing cache, result differs');
        $this->assertEquals(12345.011111, $res3, 'after clearing cache, result reflect new values');
    }

    public function testFormulaOverrideNormalResult()
    {
        $service = $this->getNewCalculator();

        // Define filter1 to actually be the result of a formula
        $formula = new \Application\Model\Rule\Formula();
        $formula->setFormula('=0.5');
        $filterRule = new \Application\Model\Rule\FilterRule();
        $filterRule->setPart($this->part)->setFilter($this->filter1)->setQuestionnaire($this->questionnaire)->setRule($formula);

        $this->assertEquals(0.5, $service->computeFilter($this->filter1->getId(), $this->questionnaire, $this->part->getId()));
    }

    public function testFormulaSyntax()
    {
        $service = $this->getNewCalculator();

        // Create a stub for the FilterRepository class with predetermined values, so we don't have to mess with database
        $nullFilter = $this->getNewModelWithId('\Application\Model\Filter');

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
        $stubQuestionnaireFormulaRepository = $this->getMock('\Application\Repository\Rule\QuestionnaireFormulaRepository', array('getOneByQuestionnaire'), array(), '', false);
        $stubQuestionnaireFormulaRepository->expects($this->any())
                ->method('getOneByQuestionnaire')
                ->will($this->returnValue($questionnaireFormula)
        );
        $service->setQuestionnaireFormulaRepository($stubQuestionnaireFormulaRepository);
        $this->assertEquals($questionnaireFormula, $stubQuestionnaireFormulaRepository->getOneByQuestionnaire(1, 2, 3));

        $formula = new \Application\Model\Rule\Formula();
        $filterRule = new \Application\Model\Rule\FilterRule();
        $filterRule->setRule($formula)
                ->setQuestionnaire($this->questionnaire)
                ->setPart($this->part)
                ->setFilter($this->filter11);

        $formula->setFormula('=(3 + 7) * SUM(2, 3)');
        $this->assertEquals(50, $service->computeFormula($filterRule), 'should be able to handle standard Excel things');

        // Filter values
        $formula->setFormula('= 10 + {F#2,Q#34,P#' . $this->part->getId() . '}');
        $this->assertEquals(10.101, $service->computeFormula($filterRule), 'should be able to refer a Filter value');

        $formula->setFormula('= 10 + {F#current,Q#current,P#current}');
        $this->assertEquals(10.101, $service->computeFormula($filterRule), 'should be able to refer a Filter value with same Questionnaire and Part');

        $formula->setFormula('= 10 + {F#' . $nullFilter->getId() . ',Q#34,P#' . $this->part->getId() . '}');
        $this->assertEquals(10, $service->computeFormula($filterRule), 'should be able to refer a Filter value which is NULL');

        // QuestionnaireFormula values
        $formula->setFormula('={Fo#12,Q#34,P#' . $this->part->getId() . '}');
        $this->assertEquals(5, $service->computeFormula($filterRule), 'should be able to refer a QuestionnaireFormula');

        $formula->setFormula('={Fo#12,Q#current,P#current}');
        $this->assertEquals(5, $service->computeFormula($filterRule), 'should be able to refer a QuestionnaireFormula with same Questionnaire and Part');

        $questionnaireFormula->getFormula()->setFormula('=NULL');
        $formula->setFormula('=7 + {Fo#12,Q#current,P#current}');
        $this->assertEquals(7, $service->computeFormula($filterRule), 'should be able to refer a QuestionnaireFormula which is NULL');

        // Unnoficial filter names
        $formula->setFormula('={F#12,Q#34}');
        $this->assertNull($service->computeFormula($filterRule), 'refering a non-existing Unofficial Filter name, returns null');

        // Inject our unnofficial filter
        $unofficialFilter = $this->getNewModelWithId('\Application\Model\Filter')->setName('unofficial with "double quotes"');
        $unofficialFilter->setQuestionnaire($this->questionnaire)->setOfficialFilter($this->filter1);

        $formula->setFormula('=ISTEXT({F#' . $unofficialFilter->getOfficialFilter()->getId() . ',Q#' . $unofficialFilter->getQuestionnaire()->getId() . '})');
        $this->assertTrue($service->computeFormula($filterRule), 'should be able to refer an Unofficial Filter name');
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
        $this->assertEquals(0.1111, $this->getNewCalculator()->computeFormula($fitlerRule), 'should fallback to filter value without any formulas');

        // Same result as above, but with different use of our API
        $this->assertEquals(0.1111, $this->getNewCalculator()->computeFilter($this->filter1->getId(), $this->questionnaire, $this->part->getId()), 'should fallback to filter value without any formulas');

        // We now add a second formula, that will be used as a fallback from first one
        $formula2 = new \Application\Model\Rule\Formula();
        $formula2->setFormula('=8 * 2');

        $fitlerRule2 = new \Application\Model\Rule\FilterRule();
        $fitlerRule2->setRule($formula2)
                ->setFilter($this->filter1)
                ->setQuestionnaire($this->questionnaire)
                ->setPart($this->part);

        $this->assertEquals(16, $this->getNewCalculator()->computeFilter($this->filter1->getId(), $this->questionnaire, $this->part->getId()), 'should fallback to second formula');
    }

}
