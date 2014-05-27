<?php

namespace ApplicationTest\Model;

class PopulationTest extends \ApplicationTest\Model\AbstractModel
{

    public function testQuestionnaireRelation()
    {
        $questionnaire = new \Application\Model\Questionnaire();
        $population = new \Application\Model\Population();
        $this->assertCount(0, $questionnaire->getPopulations(), 'collection is initialized on creation');

        $population->setQuestionnaire($questionnaire);
        $this->assertCount(1, $questionnaire->getPopulations(), 'questionnaire must be notified when population is added');
        $this->assertSame($population, $questionnaire->getPopulations()->first(), 'original population can be retrieved from questionnaire');
    }

}
