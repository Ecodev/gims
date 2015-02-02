<?php

namespace Application\Service\Syntax\BeforeRegression;

use Application\Model\Rule\AbstractQuestionnaireUsage;
use Application\Service\Calculator\Calculator;
use Application\Service\Syntax\Parser;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Replace {Q#34,P#56} with population data
 */
class PopulationValue extends AbstractToken
{

    public function getPattern()
    {
        return '/\{Q#(\d+|current),P#(\d+|current)\}/';
    }

    public function replace(Calculator $calculator, array $matches, AbstractQuestionnaireUsage $usage, ArrayCollection $alreadyUsedFormulas, $useSecondStepRules)
    {
        $questionnaireId = $matches[1];
        $partId = $this->getPartId($matches[2], $usage);

        $questionnaire = $questionnaireId == 'current' ? $usage->getQuestionnaire() : $calculator->getQuestionnaireRepository()->findOneById($questionnaireId);
        \Application\Module::getServiceManager()->get('Calculator\Cache')->record('questionnaire:' . $questionnaire->getId());

        return $calculator->getPopulationRepository()->getPopulationByQuestionnaire($questionnaire, $partId);
    }

    public function getStructure(array $matches, Parser $parser)
    {
        return [
            'type' => 'populationValue',
            'questionnaire' => [
                'id' => $matches[1],
                'name' => $parser->getQuestionnaireName($matches[1]),
            ],
            'part' => [
                'id' => $matches[2],
                'name' => $parser->getPartName($matches[2]),
            ],
        ];
    }
}
