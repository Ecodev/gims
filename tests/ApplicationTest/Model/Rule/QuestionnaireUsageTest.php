<?php

namespace ApplicationTest\Model\Rule;

/**
 * @group Model
 */
class QuestionnaireUsageTest extends \ApplicationTest\Model\AbstractModel
{

    public function testRelations()
    {
        $usage = new \Application\Model\Rule\QuestionnaireUsage();

        // Test Rule
        $rule = new \Application\Model\Rule\Rule();
        $this->assertCount(0, $rule->getQuestionnaireUsages(), 'collection is initialized on creation');
        $usage->setRule($rule);
        $this->assertCount(1, $rule->getQuestionnaireUsages(), 'rule must be notified when usage is added');
        $this->assertSame($usage, $rule->getQuestionnaireUsages()->first(), 'original usage can be retrieved from rule');

        // Test Questionnaire
        $questionnaire = new \Application\Model\Questionnaire();
        $this->assertCount(0, $questionnaire->getQuestionnaireUsages(), 'collection is initialized on creation');
        $usage->setQuestionnaire($questionnaire);
        $this->assertCount(1, $questionnaire->getQuestionnaireUsages(), 'questionnaire must be notified when usage is added');
        $this->assertSame($usage, $questionnaire->getQuestionnaireUsages()->first(), 'original usage can be retrieved from questionnaire');
    }

}
