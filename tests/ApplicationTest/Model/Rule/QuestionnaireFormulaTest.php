<?php

namespace ApplicationTest\Model;

class QuestionnaireFormulaTest extends AbstractModel
{

    public function testQuestionnaireRelation()
    {
        $questionnaire = new \Application\Model\Questionnaire();
        $relation = new \Application\Model\Rule\QuestionnaireFormula();
        $this->assertCount(0, $questionnaire->getQuestionnaireFormulas(), 'collection is initialized on creation');

        $relation->setQuestionnaire($questionnaire);
        $this->assertCount(1, $questionnaire->getQuestionnaireFormulas(), 'questionnaire must be notified when rule is added');
        $this->assertSame($relation, $questionnaire->getQuestionnaireFormulas()->first(), 'original rule can be retreived from questionnaire');
    }

}
