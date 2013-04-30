<?php

namespace ApplicationTest\Service\Calculator;

use Application\Service\Calculator\Calculator;

class CalculatorTest extends \ApplicationTest\Controller\AbstractController
{

    /**
     * @var \Application\Model\Filter
     */
    protected $filter1;

    /**
     * @var \Application\Model\Filter
     */
    protected $filter11;

    /**
     * @var \Application\Model\Filter
     */
    protected $filter12;

    /**
     * @var \Application\Model\Filter
     */
    protected $filter13;

    /**
     * @var \Application\Model\Filter
     */
    protected $filter131;

    /**
     * @var \Application\Model\Filter
     */
    protected $filter132;

    /**
     * @var \Application\Model\Filter
     */
    protected $filter14;

    /**
     * @var \Application\Model\Filter
     */
    protected $filter141;

    /**
     * @var \Application\Model\Filter
     */
    protected $filter142;

    /**
     * @var \Application\Model\Filter
     */
    protected $filter2;

    /**
     * @var \Application\Model\Filter
     */
    protected $filter21;

    /**
     * @var \Application\Model\Filter
     */
    protected $filter3;

    /**
     * @var \Application\Model\Filter
     */
    protected $filter31;

    /**
     * @var \Application\Model\Filter
     */
    protected $filter32;

    /**
     * @var \Application\Model\Questionnaire
     */
    protected $questionnaire;

    /**
     * @var \Application\Model\Question
     */
    protected $question131;

    /**
     * @var \Application\Model\Question
     */
    protected $question132;

    /**
     * @var \Application\Model\Question
     */
    protected $question141;

    /**
     * @var \Application\Model\Question
     */
    protected $question142;

    /**
     * @var \Application\Model\Question
     */
    protected $question31;

    /**
     * @var \Application\Model\Question
     */
    protected $question32;

    /**
     * @var \Application\Model\Answer
     */
    protected $answer131;

    /**
     * @var \Application\Model\Answer
     */
    protected $answer132;

    /**
     * @var \Application\Model\Answer
     */
    protected $answer141;

    /**
     * @var \Application\Model\Answer
     */
    protected $answer142;

    /**
     * @var \Application\Model\Answer
     */
    protected $answer31;

    /**
     * @var \Application\Model\Answer
     */
    protected $answer32;

    /**
     * @var \Application\Model\Filter
     */
    protected $highFilter1;

    /**
     * @var \Application\Model\Filter
     */
    protected $highFilter2;

    /**
     * @var \Application\Model\Filter
     */
    protected $highFilter3;

    public function setUp()
    {
        parent::setUp();

        $this->filter1 = new \Application\Model\Filter('cat 1');
        $this->filter11 = new \Application\Model\Filter('cat 1.1 (sum of 1.*.1)');
        $this->filter12 = new \Application\Model\Filter('cat 1.2 (sum of 1.*.2)');
        $this->filter13 = new \Application\Model\Filter('cat 1.3');
        $this->filter131 = new \Application\Model\Filter('cat 1.3.1');
        $this->filter132 = new \Application\Model\Filter('cat 1.3.2');
        $this->filter14 = new \Application\Model\Filter('cat 1.4');
        $this->filter141 = new \Application\Model\Filter('cat 1.4.1');
        $this->filter142 = new \Application\Model\Filter('cat 1.4.2');
        $this->filter2 = new \Application\Model\Filter('cat 2');
        $this->filter21 = new \Application\Model\Filter('cat 2.1');
        $this->filter3 = new \Application\Model\Filter('cat 3 (sum of 2.* but with children as default to)');
        $this->filter31 = new \Application\Model\Filter('cat 3.1');
        $this->filter32 = new \Application\Model\Filter('cat 3.2');

        // Define tree structure
        $this->filter1->addChild($this->filter11)->addChild($this->filter12)->addChild($this->filter13)->addChild($this->filter14);
        $this->filter13->addChild($this->filter131)->addChild($this->filter132);
        $this->filter14->addChild($this->filter141)->addChild($this->filter142);
        $this->filter2->addChild($this->filter21);
        $this->filter3->addChild($this->filter31)->addChild($this->filter32);

        // Define filters with summands
        $this->filter11->addSummand($this->filter131)->addSummand($this->filter141);
        $this->filter12->addSummand($this->filter132)->addSummand($this->filter142);
        $this->filter3->addSummand($this->filter21);

        // Define questionnaire with answers for leaf filters only
        $survey = new \Application\Model\Survey();
        $survey->setCode('tst 1')->setName('Test survey 1')->setYear(2000);
        $this->questionnaire = new \Application\Model\Questionnaire();
        $this->questionnaire->setSurvey($survey);

        $this->question131 = new \Application\Model\Question();
        $this->question132 = new \Application\Model\Question();
        $this->question141 = new \Application\Model\Question();
        $this->question142 = new \Application\Model\Question();
        $this->question31 = new \Application\Model\Question();
        $this->question32 = new \Application\Model\Question();

        $this->question131->setFilter($this->filter131);
        $this->question132->setFilter($this->filter132);
        $this->question141->setFilter($this->filter141);
        $this->question142->setFilter($this->filter142);
        $this->question31->setFilter($this->filter31);
        $this->question32->setFilter($this->filter32);

        $this->answer131 = new \Application\Model\Answer();
        $this->answer132 = new \Application\Model\Answer();
        $this->answer141 = new \Application\Model\Answer();
        $this->answer142 = new \Application\Model\Answer();
        $this->answer31 = new \Application\Model\Answer();
        $this->answer32 = new \Application\Model\Answer();

        $this->answer131->setQuestionnaire($this->questionnaire)->setQuestion($this->question131)->setValueAbsolute(0.1);
        $this->answer132->setQuestionnaire($this->questionnaire)->setQuestion($this->question132)->setValueAbsolute(0.01);
        $this->answer141->setQuestionnaire($this->questionnaire)->setQuestion($this->question141)->setValueAbsolute(0.001);
        $this->answer142->setQuestionnaire($this->questionnaire)->setQuestion($this->question142)->setValueAbsolute(0.0001);
        $this->answer31->setQuestionnaire($this->questionnaire)->setQuestion($this->question31)->setValueAbsolute(0.00001);
        $this->answer32->setQuestionnaire($this->questionnaire)->setQuestion($this->question32)->setValueAbsolute(0.000001);

        $this->highFilter1 = new \Application\Model\Filter('improved');
        $this->highFilter2 = new \Application\Model\Filter('unimproved');
        $this->highFilter3 = new \Application\Model\Filter('total');

        $this->highFilter1->addChild($this->filter1);
        $this->highFilter2->addChild($this->filter2);
        $this->highFilter3->addChild($this->filter1)->addChild($this->filter2)->addChild($this->filter3);
    }

    /**
     * In those test we use a new calculator each time to avoid cache, because we will chagne filter structure on the fly
     */
    public function testComputingQuestionnaireIsCorrectt()
    {
        // Assert computing for every single filter
        $this->assertEquals($this->answer131->getValueAbsolute() + $this->answer132->getValueAbsolute() + $this->answer141->getValueAbsolute() + $this->answer142->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->filter1), 'should be the sum of unique children (excluding duplicates via summands)');
        $this->assertEquals($this->answer131->getValueAbsolute() + $this->answer141->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->filter11), 'should be the sum of summands');
        $this->assertEquals($this->answer132->getValueAbsolute() + $this->answer142->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->filter12), 'should be the sum of summands');
        $this->assertEquals($this->answer131->getValueAbsolute() + $this->answer132->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->filter13), 'should be the sum of children');
        $this->assertEquals($this->answer131->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->filter131), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer132->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->filter132), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer141->getValueAbsolute() + $this->answer142->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->filter14), 'should be the sum of children');
        $this->assertEquals($this->answer141->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->filter141), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer142->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->filter142), 'should be the answer, when answer specified');
        $this->assertNull((new Calculator())->computeQuestionnaire($this->questionnaire, $this->filter2), 'should be null, when no answer at all');
        $this->assertNull((new Calculator())->computeQuestionnaire($this->questionnaire, $this->filter21), 'should be null, when no answer at all');
        $this->assertEquals($this->answer31->getValueAbsolute() + $this->answer32->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->filter3), 'should be the sum of children, when summands have no answer');
        $this->assertEquals($this->answer31->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->filter31), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer32->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->filter32), 'should be the answer, when answer specified');


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
        $this->assertEquals($this->answer11->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->filter11), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer13->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->filter13), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer11->getValueAbsolute() + $this->answer13->getValueAbsolute() + $this->answer132->getValueAbsolute() + $this->answer141->getValueAbsolute() + $this->answer142->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->filter1), 'should be the sum of children, but with overriden values instead of computed');

        // Add part to existing answer
        $part = new \Application\Model\Part('custom');
        $this->answer142->setPart($part);

        // Assert that we take part into consideration for filering answers
        $this->assertEquals($this->answer141->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->filter14), 'should be the sum of children, but only for selected part');
        $this->assertEquals($this->answer142->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->filter14, $part), 'should be the sum of children, but only for selected part');


        // Add alternative (non-official) filter to previously unexisting answer
        $this->filter21bis = new \Application\Model\Filter('cat 2.1 bis');
        $this->filter21bis->setOfficialFilter($this->filter21);
        $this->question21bis = new \Application\Model\Question();
        $this->question21bis->setFilter($this->filter21bis);
        $this->answer21bis = new \Application\Model\Answer();
        $this->answer21bis->setQuestionnaire($this->questionnaire)->setQuestion($this->question21bis)->setValueAbsolute(0.000000001);

        // Assert that alternative filter is used for computation
        $this->assertEquals($this->answer21bis->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->filter2), 'should be the sum of children, including the answer which is specified with alternative filter');
        $this->assertEquals($this->answer21bis->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->filter21), 'should be the alternative answer, when answer is specified with alternative filter');
        $this->assertEquals($this->answer21bis->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->filter3), 'should be the sum of summands, when summands have answer');


        // Define summands to use several time cat1.4.1 (once via cat1 and once via cat1.4)
        $this->filter3->addSummand($this->filter1)->addSummand($this->filter14);
        $this->assertEquals($this->answer21bis->getValueAbsolute() + $this->answer11->getValueAbsolute() + $this->answer13->getValueAbsolute() + $this->answer132->getValueAbsolute() + $this->answer141->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->filter3), 'should not sum twice the same filter');
    }

    public function testComputingFilterIsCorrect()
    {
        $service = new \Application\Service\Calculator\Calculator();

        $this->assertNull($service->computeFilter(new \Application\Model\Filter(), $this->questionnaire), 'empty filter result is always null');
        $this->assertEquals(0.1111, $service->computeFilter($this->highFilter1, $this->questionnaire), 'when only one filter should be equal to that filter');
        $this->assertNull($service->computeFilter($this->highFilter2, $this->questionnaire), 'when only one filter is null should also be null');
        $this->assertEquals(0.111111, $service->computeFilter($this->highFilter3, $this->questionnaire), 'sum all filters');
    }

    public function testCacheOnQuestionnaireLevelIsWorking()
    {
        $service = new \Application\Service\Calculator\Calculator();

        $res1 = $service->computeQuestionnaire($this->questionnaire, $this->filter1);
        $this->answer131->setValueAbsolute((12345));
        $res2 = $service->computeQuestionnaire($this->questionnaire, $this->filter1);
        $this->assertEquals($res1, $res2, 'result should be cached and therefore be the same');

        $service2 = new \Application\Service\Calculator\Calculator();
        $res3 = $service2->computeQuestionnaire($this->questionnaire, $this->filter1);
        $this->assertNotEquals($res1, $res3, 'after clearing cache, result differs');
        $this->assertEquals(12345.0111, $res3, 'after clearing cache, result reflect new values');
    }

    public function testCacheOnFilterLevelisWorking()
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

}
