<?php

namespace ApplicationTest\Service\Calculator;

use Application\Service\Calculator\Calculator;

class CalculatorTest extends \ApplicationTest\Controller\AbstractController
{

    /**
     * @var \Application\Model\Category
     */
    protected $category1;

    /**
     * @var \Application\Model\Category
     */
    protected $category11;

    /**
     * @var \Application\Model\Category
     */
    protected $category12;

    /**
     * @var \Application\Model\Category
     */
    protected $category13;

    /**
     * @var \Application\Model\Category
     */
    protected $category131;

    /**
     * @var \Application\Model\Category
     */
    protected $category132;

    /**
     * @var \Application\Model\Category
     */
    protected $category14;

    /**
     * @var \Application\Model\Category
     */
    protected $category141;

    /**
     * @var \Application\Model\Category
     */
    protected $category142;

    /**
     * @var \Application\Model\Category
     */
    protected $category2;

    /**
     * @var \Application\Model\Category
     */
    protected $category21;

    /**
     * @var \Application\Model\Category
     */
    protected $category3;

    /**
     * @var \Application\Model\Category
     */
    protected $category31;

    /**
     * @var \Application\Model\Category
     */
    protected $category32;

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
     * @var \Application\Model\CategoryFilterComponent
     */
    protected $categoryFilterComponent1;

    /**
     * @var \Application\Model\CategoryFilterComponent
     */
    protected $categoryFilterComponent2;

    /**
     * @var \Application\Model\CategoryFilterComponent
     */
    protected $categoryFilterComponent3;

    public function setUp()
    {
        parent::setUp();

        $this->category1 = new \Application\Model\Category('cat 1');
        $this->category11 = new \Application\Model\Category('cat 1.1 (sum of 1.*.1)');
        $this->category12 = new \Application\Model\Category('cat 1.2 (sum of 1.*.2)');
        $this->category13 = new \Application\Model\Category('cat 1.3');
        $this->category131 = new \Application\Model\Category('cat 1.3.1');
        $this->category132 = new \Application\Model\Category('cat 1.3.2');
        $this->category14 = new \Application\Model\Category('cat 1.4');
        $this->category141 = new \Application\Model\Category('cat 1.4.1');
        $this->category142 = new \Application\Model\Category('cat 1.4.2');
        $this->category2 = new \Application\Model\Category('cat 2');
        $this->category21 = new \Application\Model\Category('cat 2.1');
        $this->category3 = new \Application\Model\Category('cat 3 (sum of 2.* but with children as default to)');
        $this->category31 = new \Application\Model\Category('cat 3.1');
        $this->category32 = new \Application\Model\Category('cat 3.2');

        // Define tree structure
        $this->category11->setParent($this->category1);
        $this->category12->setParent($this->category1);
        $this->category13->setParent($this->category1);
        $this->category14->setParent($this->category1);
        $this->category131->setParent($this->category13);
        $this->category132->setParent($this->category13);
        $this->category141->setParent($this->category14);
        $this->category142->setParent($this->category14);
        $this->category21->setParent($this->category2);
        $this->category31->setParent($this->category3);
        $this->category32->setParent($this->category3);

        // Define categories with summands
        $this->category11->addSummand($this->category131)->addSummand($this->category141);
        $this->category12->addSummand($this->category132)->addSummand($this->category142);
        $this->category3->addSummand($this->category21);

        // Define questionnaire with answers for leaf categories only
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

        $this->question131->setCategory($this->category131);
        $this->question132->setCategory($this->category132);
        $this->question141->setCategory($this->category141);
        $this->question142->setCategory($this->category142);
        $this->question31->setCategory($this->category31);
        $this->question32->setCategory($this->category32);

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

        $this->categoryFilterComponent1 = new \Application\Model\CategoryFilterComponent('improved');
        $this->categoryFilterComponent2 = new \Application\Model\CategoryFilterComponent('unimproved');
        $this->categoryFilterComponent3 = new \Application\Model\CategoryFilterComponent('total');

        $this->categoryFilterComponent1->addCategory($this->category1);
        $this->categoryFilterComponent2->addCategory($this->category2);
        $this->categoryFilterComponent3->addCategory($this->category1)->addCategory($this->category2)->addCategory($this->category3);
    }

    /**
     * In those test we use a new calculator each time to avoid cache, because we will chagne category structure on the fly
     */
    public function testComputingQuestionnaireIsCorrectt()
    {
        // Assert computing for every single category
        $this->assertEquals($this->answer131->getValueAbsolute() + $this->answer132->getValueAbsolute() + $this->answer141->getValueAbsolute() + $this->answer142->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->category1), 'should be the sum of unique children (excluding duplicates via summands)');
        $this->assertEquals($this->answer131->getValueAbsolute() + $this->answer141->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->category11), 'should be the sum of summands');
        $this->assertEquals($this->answer132->getValueAbsolute() + $this->answer142->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->category12), 'should be the sum of summands');
        $this->assertEquals($this->answer131->getValueAbsolute() + $this->answer132->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->category13), 'should be the sum of children');
        $this->assertEquals($this->answer131->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->category131), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer132->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->category132), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer141->getValueAbsolute() + $this->answer142->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->category14), 'should be the sum of children');
        $this->assertEquals($this->answer141->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->category141), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer142->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->category142), 'should be the answer, when answer specified');
        $this->assertNull((new Calculator())->computeQuestionnaire($this->questionnaire, $this->category2), 'should be null, when no answer at all');
        $this->assertNull((new Calculator())->computeQuestionnaire($this->questionnaire, $this->category21), 'should be null, when no answer at all');
        $this->assertEquals($this->answer31->getValueAbsolute() + $this->answer32->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->category3), 'should be the sum of children, when summands have no answer');
        $this->assertEquals($this->answer31->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->category31), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer32->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->category32), 'should be the answer, when answer specified');


        // Overwrite computed categories with an answer
        $this->question11 = new \Application\Model\Question();
        $this->question13 = new \Application\Model\Question();
        $this->question11->setCategory($this->category11);
        $this->question13->setCategory($this->category13);
        $this->answer11 = new \Application\Model\Answer();
        $this->answer13 = new \Application\Model\Answer();
        $this->answer11->setQuestionnaire($this->questionnaire)->setQuestion($this->question11)->setValueAbsolute(0.0000001);
        $this->answer13->setQuestionnaire($this->questionnaire)->setQuestion($this->question13)->setValueAbsolute(0.00000001);

        // Assert that manually specified answer override computed values
        $this->assertEquals($this->answer11->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->category11), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer13->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->category13), 'should be the answer, when answer specified');
        $this->assertEquals($this->answer11->getValueAbsolute() + $this->answer13->getValueAbsolute() + $this->answer132->getValueAbsolute() + $this->answer141->getValueAbsolute() + $this->answer142->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->category1), 'should be the sum of children, but with overriden values instead of computed');

        // Add part to existing answer
        $part = new \Application\Model\Part('custom');
        $this->answer142->setPart($part);

        // Assert that we take part into consideration for filering answers
        $this->assertEquals($this->answer141->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->category14), 'should be the sum of children, but only for selected part');
        $this->assertEquals($this->answer142->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->category14, $part), 'should be the sum of children, but only for selected part');


        // Add alternative (non-official) category to previously unexisting answer
        $this->category21bis = new \Application\Model\Category('cat 2.1 bis');
        $this->category21bis->setOfficialCategory($this->category21);
        $this->question21bis = new \Application\Model\Question();
        $this->question21bis->setCategory($this->category21bis);
        $this->answer21bis = new \Application\Model\Answer();
        $this->answer21bis->setQuestionnaire($this->questionnaire)->setQuestion($this->question21bis)->setValueAbsolute(0.000000001);

        // Assert that alternative category is used for computation
        $this->assertEquals($this->answer21bis->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->category2), 'should be the sum of children, including the answer which is specified with alternative category');
        $this->assertEquals($this->answer21bis->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->category21), 'should be the alternative answer, when answer is specified with alternative category');
        $this->assertEquals($this->answer21bis->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->category3), 'should be the sum of summands, when summands have answer');


        // Define summands to use several time cat1.4.1 (once via cat1 and once via cat1.4)
        $this->category3->addSummand($this->category1)->addSummand($this->category14);
        $this->assertEquals($this->answer21bis->getValueAbsolute() + $this->answer11->getValueAbsolute() + $this->answer13->getValueAbsolute() + $this->answer132->getValueAbsolute() + $this->answer141->getValueAbsolute(), (new Calculator())->computeQuestionnaire($this->questionnaire, $this->category3), 'should not sum twice the same category');
    }

    public function testComputingCategoryFilterComponentIsCorrect()
    {
        $service = new \Application\Service\Calculator\Calculator();

        $this->assertNull($service->computeCategoryFilterComponent(new \Application\Model\CategoryFilterComponent(), $this->questionnaire), 'empty filter result is always null');
        $this->assertEquals(0.1111, $service->computeCategoryFilterComponent($this->categoryFilterComponent1, $this->questionnaire), 'when only one category should be equal to that category');
        $this->assertNull($service->computeCategoryFilterComponent($this->categoryFilterComponent2, $this->questionnaire), 'when only one category is null should also be null');
        $this->assertEquals(0.111111, $service->computeCategoryFilterComponent($this->categoryFilterComponent3, $this->questionnaire), 'sum all categories');
    }

    public function testCacheOnQuestionnaireLevelIsWorking()
    {
        $service = new \Application\Service\Calculator\Calculator();

        $res1 = $service->computeQuestionnaire($this->questionnaire, $this->category1);
        $this->answer131->setValueAbsolute((12345));
        $res2 = $service->computeQuestionnaire($this->questionnaire, $this->category1);
        $this->assertEquals($res1, $res2, 'result should be cached and therefore be the same');

        $service2 = new \Application\Service\Calculator\Calculator();
        $res3 = $service2->computeQuestionnaire($this->questionnaire, $this->category1);
        $this->assertNotEquals($res1, $res3, 'after clearing cache, result differs');
        $this->assertEquals(12345.0111, $res3, 'after clearing cache, result reflect new values');
    }

    public function testCacheOnFilterComponentLevelisWorking()
    {
        $service = new \Application\Service\Calculator\Calculator();

        $res1 = $service->computeCategoryFilterComponent($this->categoryFilterComponent3, $this->questionnaire);
        $this->answer131->setValueAbsolute((12345));
        $res2 = $service->computeCategoryFilterComponent($this->categoryFilterComponent3, $this->questionnaire);
        $this->assertEquals($res1, $res2, 'result should be cached and therefore be the same');

        $service2 = new \Application\Service\Calculator\Calculator();
        $res3 = $service2->computeCategoryFilterComponent($this->categoryFilterComponent3, $this->questionnaire);
        $this->assertNotEquals($res1, $res3, 'after clearing cache, result differs');
        $this->assertEquals(12345.011111, $res3, 'after clearing cache, result reflect new values');
    }

}
