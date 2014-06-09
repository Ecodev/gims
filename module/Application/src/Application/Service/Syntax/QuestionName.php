<?php

namespace Application\Service\Syntax;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Service\Calculator\Calculator;
use Application\Model\Rule\AbstractQuestionnaireUsage;

/**
 * Replace {F#12,Q#34} with Question name, or NULL if no Question/Answer
 */
class QuestionName extends AbstractBasicToken
{

    public function getPattern()
    {
        return '/\{F#(\d+|current),Q#(\d+|current)\}/';
    }

    public function replace(Calculator $calculator, array $matches, AbstractQuestionnaireUsage $usage, ArrayCollection $alreadyUsedFormulas, $useSecondLevelRules)
    {
        $filterId = $matches[1];
        $questionnaireId = $matches[2];

        if ($filterId == 'current') {
            $filterId = $usage->getFilter()->getId();
        }

        if ($questionnaireId == 'current') {
            $questionnaireId = $usage->getQuestionnaire()->getId();
        }

        $questionName = $calculator->getAnswerRepository()->getQuestionNameIfNonNullAnswer($questionnaireId, $filterId);
        if (is_null($questionName)) {
            return 'NULL';
        } else {
            // Format string for Excel formula
            return '"' . str_replace('"', '""', $questionName) . '"';
        }
    }

}
