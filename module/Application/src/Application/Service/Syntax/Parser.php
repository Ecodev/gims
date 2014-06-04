<?php

namespace Application\Service\Syntax;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Service\Calculator\Calculator;
use Application\Service\Calculator\Jmp;
use Application\Model\Rule\AbstractQuestionnaireUsage;

class Parser
{

    private $basics;
    private $regressions;

    public function __construct()
    {
        $this->basics = [
            new FilterValue(),
            new QuestionName(),
            new QuestionnaireUsageValue(),
            new PopulationValue(),
            new BasicSelf(),
        ];

        $this->regressions = [
            new RegressionFilterValuesList(),
            new RegressionFilterValue(),
            new RegressionCumulatedPopulation(),
            new RegressionSelf(),
            new RegressionYear(),
        ];
    }

    public function convertBasic(Calculator $calculator, $formula, AbstractQuestionnaireUsage $usage, ArrayCollection $alreadyUsedFormulas, $useSecondLevelRules)
    {
        foreach ($this->basics as $syntax) {
            $formula = \Application\Utility::pregReplaceUniqueCallback($syntax->getPattern(), function($matches) use ($syntax, $calculator, $usage, $alreadyUsedFormulas, $useSecondLevelRules) {
                        return $syntax->replace($calculator, $matches, $usage, $alreadyUsedFormulas, $useSecondLevelRules);
                    }, $formula);
        }

        return $formula;
    }

    public function convertRegression(Jmp $calculator, $formula, $currentFilterId, array $questionnaires, $currentPartId, $year, array $years, ArrayCollection $alreadyUsedRules)
    {
        foreach ($this->regressions as $syntax) {
            $formula = \Application\Utility::pregReplaceUniqueCallback($syntax->getPattern(), function($matches) use ($syntax, $calculator, $currentFilterId, $questionnaires, $currentPartId, $year, $years, $alreadyUsedRules) {
                        return $syntax->replace($calculator, $matches, $currentFilterId, $questionnaires, $currentPartId, $year, $years, $alreadyUsedRules);
                    }, $formula);
        }

        return $formula;
    }

    public function getAllFake()
    {
        $all = self::getAll();
        foreach ($all as $pattern => $syntax) {

        }
    }

    protected function indexSyntaxes(array $syntaxes)
    {
        $indexedSyntaxes = [];
        foreach ($syntaxes as $sy) {
            $indexedSyntaxes[$sy->getPattern()] = $sy;
        }

        return $indexedSyntaxes;
    }

}
