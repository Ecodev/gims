<?php

namespace Application\Service\Syntax\BeforeRegression;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Service\Calculator\Calculator;
use Application\Model\Rule\AbstractQuestionnaireUsage;
use Application\Service\Syntax\Parser;

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

        return $calculator->getPopulationRepository()->getPopulationByQuestionnaire($questionnaire, $partId);
    }

    public function getStructure(array $matches, Parser $parser)
    {
        return [
            'type' => 'populationValue',
            'questionnaire' => $parser->getQuestionnaireName($matches[1]),
            'part' => $parser->getPartName($matches[2]),
        ];
    }
}
