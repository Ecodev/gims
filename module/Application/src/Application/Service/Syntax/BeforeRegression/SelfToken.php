<?php

namespace Application\Service\Syntax\BeforeRegression;

use Application\Model\Rule\AbstractQuestionnaireUsage;
use Application\Service\Calculator\Calculator;
use Application\Service\Syntax\Parser;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Replace {self} with computed value without this formula
 */
class SelfToken extends AbstractToken implements \Application\Service\Syntax\BothContextInterface
{

    public function getPattern()
    {
        return '/\{self\}/';
    }

    public function replace(Calculator $calculator, array $matches, AbstractQuestionnaireUsage $usage, ArrayCollection $alreadyUsedFormulas, $useSecondStepRules)
    {
        $value = $calculator->computeFilter($usage->getFilter()->getId(), $usage->getQuestionnaire()->getId(), $usage->getPart()->getId(), $useSecondStepRules, $alreadyUsedFormulas);

        return is_null($value) ? 'NULL' : $value;
    }

    public function getStructure(array $matches, Parser $parser)
    {
        return [
            'type' => 'self',
        ];
    }
}
