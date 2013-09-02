<?php

namespace ApplicationTest\Service\Calculator;

abstract class AbstractCalculator extends \ApplicationTest\Controller\AbstractController
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
     * @var \Application\Model\Question\NumericQuestion
     */
    protected $question131;

    /**
     * @var \Application\Model\Question\NumericQuestion
     */
    protected $question132;

    /**
     * @var \Application\Model\Question\NumericQuestion
     */
    protected $question141;

    /**
     * @var \Application\Model\Question\NumericQuestion
     */
    protected $question142;

    /**
     * @var \Application\Model\Question\NumericQuestion
     */
    protected $question31;

    /**
     * @var \Application\Model\Question\NumericQuestion
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

    /**
     * @var \Application\Model\Part
     */
    protected $part;

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

        $this->question131 = new \Application\Model\Question\NumericQuestion();
        $this->question132 = new \Application\Model\Question\NumericQuestion();
        $this->question141 = new \Application\Model\Question\NumericQuestion();
        $this->question142 = new \Application\Model\Question\NumericQuestion();
        $this->question31 = new \Application\Model\Question\NumericQuestion();
        $this->question32 = new \Application\Model\Question\NumericQuestion();

        $this->question131->setFilter($this->filter131);
        $this->question132->setFilter($this->filter132);
        $this->question141->setFilter($this->filter141);
        $this->question142->setFilter($this->filter142);
        $this->question31->setFilter($this->filter31);
        $this->question32->setFilter($this->filter32);

        // Create a stub for the Part class, so we can tell it represent the total part (it's usually a read-only property)
        $this->part = $this->getMock('\Application\Model\Part', array('isTotal'));
        $this->part->expects($this->any())
                ->method('isTotal')
                ->will($this->returnValue(true));

        $this->answer131 = new \Application\Model\Answer();
        $this->answer132 = new \Application\Model\Answer();
        $this->answer141 = new \Application\Model\Answer();
        $this->answer142 = new \Application\Model\Answer();
        $this->answer31 = new \Application\Model\Answer();
        $this->answer32 = new \Application\Model\Answer();

        $this->answer131->setPart($this->part)->setQuestionnaire($this->questionnaire)->setQuestion($this->question131)->setValueAbsolute(0.1);
        $this->answer132->setPart($this->part)->setQuestionnaire($this->questionnaire)->setQuestion($this->question132)->setValueAbsolute(0.01);
        $this->answer141->setPart($this->part)->setQuestionnaire($this->questionnaire)->setQuestion($this->question141)->setValueAbsolute(0.001);
        $this->answer142->setPart($this->part)->setQuestionnaire($this->questionnaire)->setQuestion($this->question142)->setValueAbsolute(0.0001);
        $this->answer31->setPart($this->part)->setQuestionnaire($this->questionnaire)->setQuestion($this->question31)->setValueAbsolute(0.00001);
        $this->answer32->setPart($this->part)->setQuestionnaire($this->questionnaire)->setQuestion($this->question32)->setValueAbsolute(0.000001);

        $this->highFilter1 = new \Application\Model\Filter('improved');
        $this->highFilter2 = new \Application\Model\Filter('unimproved');
        $this->highFilter3 = new \Application\Model\Filter('total');

        $this->highFilter1->addChild($this->filter1);
        $this->highFilter2->addChild($this->filter2);
        $this->highFilter3->addChild($this->filter1)->addChild($this->filter2)->addChild($this->filter3);
    }

}
