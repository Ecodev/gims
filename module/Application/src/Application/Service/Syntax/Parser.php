<?php

namespace Application\Service\Syntax;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Service\Calculator\Calculator;
use Application\Service\Calculator\Jmp;
use Application\Model\Rule\AbstractQuestionnaireUsage;

/**
 * Parser used to convert GIMS formula syntax into pure Excel formula syntax.
 */
class Parser
{

    /**
     * @var array
     */
    private $basics;

    /**
     * @var array
     */
    private $regressions;

    /**
     * Constructor
     */
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

    /**
     * Get an array of basic tokens
     * @return AbstractBasicToken[]
     */
    public function getBasicTokens()
    {
        return $this->basics;
    }

    /**
     * Get an array of basic tokens
     * @return AbstractRegressionToken[]
     */
    public function getRegressionTokens()
    {
        return $this->regressions;
    }

    /**
     * Convert the given GIMS formula into an Excel formula by using basic tokens
     * @param \Application\Service\Calculator\Calculator $calculator
     * @param string $formula GIMS formula
     * @param \Application\Model\Rule\AbstractQuestionnaireUsage $usage
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadyUsedFormulas
     * @param boolean $useSecondLevelRules
     * @return string
     */
    public function convertBasic(Calculator $calculator, $formula, AbstractQuestionnaireUsage $usage, ArrayCollection $alreadyUsedFormulas, $useSecondLevelRules)
    {
        foreach ($this->basics as $token) {
            $formula = \Application\Utility::pregReplaceUniqueCallback($token->getPattern(), function($matches) use ($token, $calculator, $usage, $alreadyUsedFormulas, $useSecondLevelRules) {
                        return $token->replace($calculator, $matches, $usage, $alreadyUsedFormulas, $useSecondLevelRules);
                    }, $formula);
        }

        return $formula;
    }

    /**
     * Convert the given GIMS formula into an Excel formula by using regression tokens
     * @param \Application\Service\Calculator\Jmp $calculator
     * @param string $formula GIMS formula
     * @param itneger $currentFilterId
     * @param array $questionnaires
     * @param integer $currentPartId
     * @param integer $year
     * @param array $years
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadyUsedRules
     * @return string
     */
    public function convertRegression(Jmp $calculator, $formula, $currentFilterId, array $questionnaires, $currentPartId, $year, array $years, ArrayCollection $alreadyUsedRules)
    {
        foreach ($this->regressions as $token) {
            $formula = \Application\Utility::pregReplaceUniqueCallback($token->getPattern(), function($matches) use ($token, $calculator, $currentFilterId, $questionnaires, $currentPartId, $year, $years, $alreadyUsedRules) {
                        return $token->replace($calculator, $matches, $currentFilterId, $questionnaires, $currentPartId, $year, $years, $alreadyUsedRules);
                    }, $formula);
        }

        return $formula;
    }

    /**
     * Compute a pure Excel formula and return its result
     * @param string $convertedFormula
     * @return null|float
     */
    public function computeExcelFormula($convertedFormula)
    {
        $result = \PHPExcel_Calculation::getInstance()->_calculateFormulaValue($convertedFormula);

        // In some edge cases, it may happen that we get FALSE or empty double quotes as result,
        // we need to convert it to NULL, otherwise it will be converted to
        // 0 later, which is not correct. Eg: '=IF(FALSE, NULL, NULL)', or '=IF(FALSE,NULL,"")'
        if ($result === false || $result === '""') {
            $result = null;
        }

        return $result;
    }

}
