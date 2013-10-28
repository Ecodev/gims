<?php

namespace ApplicationTest\Model;

class QuestionnaireUsageTest extends AbstractModel
{

    public function testQuestionnaireRelation()
    {
        $questionnaire = new \Application\Model\Questionnaire();
        $usage = new \Application\Model\Rule\QuestionnaireUsage();
        $this->assertCount(0, $questionnaire->getQuestionnaireUsages(), 'collection is initialized on creation');

        $usage->setQuestionnaire($questionnaire);
        $this->assertCount(1, $questionnaire->getQuestionnaireUsages(), 'questionnaire must be notified when rule is added');
        $this->assertSame($usage, $questionnaire->getQuestionnaireUsages()->first(), 'original rule can be retreived from questionnaire');
    }

}
