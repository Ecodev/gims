<?php

namespace ApplicationTest\Service\Calculator;

/**
 * @group Calculator
 */
class BasicCalculatorTest extends AbstractCalculator
{

    /**
     * In those test we use a new calculator each time to avoid cache, because we will chagne filter structure on the fly
     */
    public function testComputingQuestionnaireIsCorrect()
    {
        // Assert computing for every single filter
        $this->assertEquals($this->answer131->getValuePercent() + $this->answer132->getValuePercent() + $this->answer141->getValuePercent() + $this->answer142->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter1->getId(), $this->questionnaire->getId(), $this->part1->getId()), 'should be the sum of unique children (excluding duplicates via summands)');
        $this->assertEquals($this->answer131->getValuePercent() + $this->answer141->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter11->getId(), $this->questionnaire->getId(), $this->part1->getId()), 'should be the sum of summands');
        $this->assertEquals($this->answer132->getValuePercent() + $this->answer142->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter12->getId(), $this->questionnaire->getId(), $this->part1->getId()), 'should be the sum of summands');
        $this->assertEquals($this->answer131->getValuePercent() + $this->answer132->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter13->getId(), $this->questionnaire->getId(), $this->part1->getId()), 'should be the sum of children');
        $this->assertEquals($this->answer131->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter131->getId(), $this->questionnaire->getId(), $this->part1->getId()), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer132->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter132->getId(), $this->questionnaire->getId(), $this->part1->getId()), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer141->getValuePercent() + $this->answer142->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter14->getId(), $this->questionnaire->getId(), $this->part1->getId()), 'should be the sum of children');
        $this->assertEquals($this->answer141->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter141->getId(), $this->questionnaire->getId(), $this->part1->getId()), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer142->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter142->getId(), $this->questionnaire->getId(), $this->part1->getId()), 'should be the answer, when answer specified');
        $this->assertNull($this->getNewCalculator()->computeFilter($this->filter2->getId(), $this->questionnaire->getId(), $this->part1->getId()), 'should be null, when no answer at all');
        $this->assertNull($this->getNewCalculator()->computeFilter($this->filter21->getId(), $this->questionnaire->getId(), $this->part1->getId()), 'should be null, when no answer at all');
        $this->assertEquals($this->answer31->getValuePercent() + $this->answer32->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter3->getId(), $this->questionnaire->getId(), $this->part1->getId()), 'should be the sum of children, when summands have no answer');
        $this->assertEquals($this->answer31->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter31->getId(), $this->questionnaire->getId(), $this->part1->getId()), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer32->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter32->getId(), $this->questionnaire->getId(), $this->part1->getId()), 'should be the answer, when answer specified');

        // Overwrite computed filters with an answer
        $this->question11 = new \Application\Model\Question\NumericQuestion();
        $this->question13 = new \Application\Model\Question\NumericQuestion();
        $this->question11->setFilter($this->filter11);
        $this->question13->setFilter($this->filter13);
        $this->answer11 = new \Application\Model\Answer();
        $this->answer13 = new \Application\Model\Answer();
        $this->answer11->setPart($this->part1)->setQuestionnaire($this->questionnaire)->setQuestion($this->question11)->setValuePercent(0.0000001);
        $this->answer13->setPart($this->part1)->setQuestionnaire($this->questionnaire)->setQuestion($this->question13)->setValuePercent(0.00000001);

        // Assert that manually specified answer override computed values
        $this->assertEquals($this->answer11->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter11->getId(), $this->questionnaire->getId(), $this->part1->getId()), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer13->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter13->getId(), $this->questionnaire->getId(), $this->part1->getId()), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer11->getValuePercent() + $this->answer13->getValuePercent() + $this->answer132->getValuePercent() + $this->answer141->getValuePercent() + $this->answer142->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter1->getId(), $this->questionnaire->getId(), $this->part1->getId()), 'should be the sum of children, but with overriden values instead of computed');

        // Add part to existing answer
        $part = $this->getNewModelWithId('\Application\Model\Part')->setName('custom');
        $this->answer142->setPart($part);

        // Assert that we take part into consideration for filering answers
        $this->assertEquals($this->answer141->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter14->getId(), $this->questionnaire->getId(), $this->part1->getId()), 'should be the sum of children, but only for selected part (1)');
        $this->assertEquals($this->answer142->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter14->getId(), $this->questionnaire->getId(), $part->getId()), 'should be the sum of children, but only for selected part (2)');

        // Define summands to use several time cat1.4.1 (once via cat1 and once via cat1.4)
        $this->filter3->addSummand($this->filter1)->addSummand($this->filter14);
        $this->assertEquals($this->answer11->getValuePercent() + $this->answer13->getValuePercent() + $this->answer132->getValuePercent() + $this->answer141->getValuePercent(), $this->getNewCalculator()->computeFilter($this->filter3->getId(), $this->questionnaire->getId(), $this->part1->getId()), 'should not sum twice the same filter');
    }

    public function testComputingFilterIsCorrect()
    {
        $service = $this->getNewCalculator();

        $this->assertNull($service->computeFilter($this->getNewModelWithId('\Application\Model\Filter')->getId(), $this->questionnaire->getId(), $this->part1->getId()), 'empty filter result is always null');
        $this->assertEquals(0.1111, $service->computeFilter($this->highFilter1->getId(), $this->questionnaire->getId(), $this->part1->getId()), 'when only one filter should be equal to that filter');
        $this->assertNull($service->computeFilter($this->highFilter2->getId(), $this->questionnaire->getId(), $this->part1->getId()), 'when only one filter is null should also be null');
        $this->assertEquals(0.111111, $service->computeFilter($this->highFilter3->getId(), $this->questionnaire->getId(), $this->part1->getId()), 'sum all filters');
    }

    public function testCacheOnFilter()
    {
        $service = $this->getNewCalculator();

        $res1 = $service->computeFilter($this->highFilter3->getId(), $this->questionnaire->getId(), $this->part1->getId());
        $this->answer131->setValuePercent((12345));
        $res2 = $service->computeFilter($this->highFilter3->getId(), $this->questionnaire->getId(), $this->part1->getId());
        $this->assertEquals($res1, $res2, 'result should be cached and therefore be the same');

        $service2 = $this->getNewCalculator();
        $res3 = $service2->computeFilter($this->highFilter3->getId(), $this->questionnaire->getId(), $this->part1->getId());
        $this->assertNotEquals($res1, $res3, 'after clearing cache, result differs');
        $this->assertEquals(12345.011111, $res3, 'after clearing cache, result reflect new values');
    }

    public function testFormulaOverrideNormalResult()
    {
        $service = $this->getNewCalculator();

        // Define filter1 to actually be the result of a formula
        $rule = new \Application\Model\Rule\Rule();
        $rule->setFormula('=0.5');
        $filterQuestionnaireUsage = new \Application\Model\Rule\FilterQuestionnaireUsage();
        $filterQuestionnaireUsage->setPart($this->part1)->setFilter($this->filter1)->setQuestionnaire($this->questionnaire)->setRule($rule);

        $this->assertEquals(0.5, $service->computeFilter($this->filter1->getId(), $this->questionnaire->getId(), $this->part1->getId()));
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
                            array('56', $this->part1),
        )));
        $service->setPartRepository($stubPartRepository);

        // Create a stub for the QuestionnaireUsageRepository class with predetermined values, so we don't have to mess with database
        $questionnaireUsage = new \Application\Model\Rule\QuestionnaireUsage();
        $questionnaireUsage->setRule((new \Application\Model\Rule\Rule())->setFormula('=2+3'))
                ->setQuestionnaire($this->questionnaire)
                ->setPart($this->part1);
        $stubQuestionnaireUsageRepository = $this->getMock('\Application\Repository\Rule\QuestionnaireUsageRepository', array('getOneByQuestionnaire'), array(), '', false);
        $stubQuestionnaireUsageRepository->expects($this->any())
                ->method('getOneByQuestionnaire')
                ->will($this->returnValue($questionnaireUsage)
        );
        $service->setQuestionnaireUsageRepository($stubQuestionnaireUsageRepository);
        $this->assertEquals($questionnaireUsage, $stubQuestionnaireUsageRepository->getOneByQuestionnaire(1, 2, 3));

        $rule = new \Application\Model\Rule\Rule();
        $filterQuestionnaireUsage = new \Application\Model\Rule\FilterQuestionnaireUsage();
        $filterQuestionnaireUsage->setRule($rule)
                ->setQuestionnaire($this->questionnaire)
                ->setPart($this->part1)
                ->setFilter($this->filter11);

        $rule->setFormula('=(3 + 7) * SUM(2, 3)');
        $this->assertEquals(50, $service->computeFormulaBasic($filterQuestionnaireUsage), 'should be able to handle standard Excel things');

        // Filter values
        $rule->setFormula('= 10 + {F#2,Q#' . $this->questionnaire->getId() . ',P#' . $this->part1->getId() . '}');
        $this->assertEquals(10.101, $service->computeFormulaBasic($filterQuestionnaireUsage), 'should be able to refer a Filter value');

        $rule->setFormula('= 10 + {F#current,Q#current,P#current}');
        $this->assertEquals(10.101, $service->computeFormulaBasic($filterQuestionnaireUsage), 'should be able to refer a Filter value with same Questionnaire and Part');

        $rule->setFormula('= 10 + {F#' . $nullFilter->getId() . ',Q#' . $this->questionnaire->getId() . ',P#' . $this->part1->getId() . '}');
        $this->assertEquals(10, $service->computeFormulaBasic($filterQuestionnaireUsage), 'should be able to refer a Filter value which is NULL');

        // QuestionnaireUsage values
        $rule->setFormula('={R#12,Q#' . $this->questionnaire->getId() . ',P#' . $this->part1->getId() . '}');
        $this->assertEquals(5, $service->computeFormulaBasic($filterQuestionnaireUsage), 'should be able to refer a QuestionnaireUsage');

        $rule->setFormula('={R#12,Q#current,P#current}');
        $this->assertEquals(5, $service->computeFormulaBasic($filterQuestionnaireUsage), 'should be able to refer a QuestionnaireUsage with same Questionnaire and Part');

        $questionnaireUsage->getRule()->setFormula('=NULL');
        $rule->setFormula('=7 + {R#12,Q#current,P#current}');
        $this->assertEquals(7, $service->computeFormulaBasic($filterQuestionnaireUsage), 'should be able to refer a QuestionnaireUsage which is NULL');

        // Non-existing question names
        $rule->setFormula('={F#12,Q#' . $this->questionnaire->getId() . '}');
        $this->assertNull($service->computeFormulaBasic($filterQuestionnaireUsage), 'refering a non-existing Question name, returns null');

        // Question names with double quotes
        $this->question131->setName('question with "double quotes"');
        $rule->setFormula('=ISTEXT({F#' . $this->filter131->getId() . ',Q#' . $this->questionnaire->getId() . '})');
        $this->assertTrue($service->computeFormulaBasic($filterQuestionnaireUsage), 'should be able to refer a Question name');
    }

    public function testFormulaSyntaxSelf()
    {
        $rule = new \Application\Model\Rule\Rule();

        $fitlerRule = new \Application\Model\Rule\FilterQuestionnaireUsage();
        $fitlerRule->setRule($rule)
                ->setFilter($this->filter1)
                ->setQuestionnaire($this->questionnaire)
                ->setPart($this->part1);

        $rule->setFormula('={self}');
        $this->assertEquals(0.1111, $this->getNewCalculator()->computeFormulaBasic($fitlerRule), 'should fallback to filter value without any formulas');

        // Same result as above, but with different use of our API
        $this->assertEquals(0.1111, $this->getNewCalculator()->computeFilter($this->filter1->getId(), $this->questionnaire->getId(), $this->part1->getId()), 'should fallback to filter value without any formulas');

        // We now add a second formula, that will be used as a fallback from first one
        $rule2 = new \Application\Model\Rule\Rule();
        $rule2->setFormula('=8 * 2');

        $fitlerRule2 = new \Application\Model\Rule\FilterQuestionnaireUsage();
        $fitlerRule2->setRule($rule2)
                ->setFilter($this->filter1)
                ->setQuestionnaire($this->questionnaire)
                ->setPart($this->part1);

        $this->assertEquals(16, $this->getNewCalculator()->computeFilter($this->filter1->getId(), $this->questionnaire->getId(), $this->part1->getId()), 'should fallback to second formula');
    }

    public function testOveriddenFilters()
    {
        $calculator = $this->getNewCalculator();
        $this->assertEquals($this->answer32->getValuePercent(), $calculator->computeFilter($this->filter32->getId(), $this->questionnaire->getId(), $this->part1->getId()), 'should be the answer, when answer specified');

        $overridenValue = 345;
        $calculator->setOverriddenFilters([
            $this->questionnaire->getId() => [
                $this->filter32->getId() => [
                    $this->part1->getId() => $overridenValue,
                ],
            ],
        ]);
        $this->assertEquals($overridenValue, $calculator->computeFilter($this->filter32->getId(), $this->questionnaire->getId(), $this->part1->getId()), 'exact same call should now return overriden value');

        $calculator->setOverriddenFilters([]);
        $this->assertEquals($this->answer32->getValuePercent(), $calculator->computeFilter($this->filter32->getId(), $this->questionnaire->getId(), $this->part1->getId()), 'after reseting overrides, should return original value, from cache');
    }

}
