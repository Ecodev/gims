<?php

namespace ApplicationTest\Model\Rule;

/**
 * @group Model
 */
class FilterQuestionnaireUsageTest extends \ApplicationTest\Model\AbstractModel
{

    public function testRelations()
    {
        $usage = new \Application\Model\Rule\FilterQuestionnaireUsage();

        // Test Rule
        $rule = new \Application\Model\Rule\Rule();
        $this->assertCount(0, $rule->getFilterQuestionnaireUsages(), 'collection is initialized on creation');
        $usage->setRule($rule);
        $this->assertCount(1, $rule->getFilterQuestionnaireUsages(), 'rule must be notified when usage is added');
        $this->assertSame($usage, $rule->getFilterQuestionnaireUsages()->first(), 'original usage can be retrieved from rule');

        // Test filter
        $filter = new \Application\Model\Filter();
        $this->assertCount(0, $filter->getFilterQuestionnaireUsages(), 'collection is initialized on creation');
        $usage->setFilter($filter);
        $this->assertCount(1, $filter->getFilterQuestionnaireUsages(), 'filter must be notified when usage is added');
        $this->assertSame($usage, $filter->getFilterQuestionnaireUsages()->first(), 'original usage can be retrieved from filter');

        // Test questionnaire
        $questionnaire = new \Application\Model\Questionnaire();
        $this->assertCount(0, $questionnaire->getFilterQuestionnaireUsages(), 'collection is initialized on creation');
        $usage->setQuestionnaire($questionnaire);
        $this->assertCount(1, $questionnaire->getFilterQuestionnaireUsages(), 'questionnaire must be notified when usage is added');
        $this->assertSame($usage, $questionnaire->getFilterQuestionnaireUsages()->first(), 'original usage can be retrieved from questionnaire');
    }
}
