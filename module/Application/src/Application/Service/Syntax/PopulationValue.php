<?php

namespace Application\Service\Syntax;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Service\Calculator\Calculator;
use Application\Model\Rule\AbstractQuestionnaireUsage;

/**
 * Replace {Q#34,P#56} with population data
 */
class PopulationValue extends AbstractBasicToken
{

    public function getPattern()
    {
        return '/\{Q#(\d+|current),P#(\d+|current)\}/';
    }

    public function replace(Calculator $calculator, array $matches, AbstractQuestionnaireUsage $usage, ArrayCollection $alreadyUsedFormulas, $useSecondLevelRules)
    {
        $questionnaireId = $matches[1];
        $partId = $matches[2];

        $questionnaire = $questionnaireId == 'current' ? $usage->getQuestionnaire() : $calculator->getQuestionnaireRepository()->findOneById($questionnaireId);

        if ($partId == 'current') {
            $partId = $usage->getPart()->getId();
        }

        return $calculator->getPopulationRepository()->getOneByQuestionnaire($questionnaire, $partId)->getPopulation();
    }

}
