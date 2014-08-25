<?php

namespace Application\Service\Syntax;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Service\Calculator\Calculator;
use Application\Model\Rule\AbstractQuestionnaireUsage;

/**
 * Parser used to convert GIMS formula syntax into pure Excel formula syntax.
 */
class Parser
{

    use \Zend\ServiceManager\ServiceLocatorAwareTrait;

use \Application\Traits\EntityManagerAware;

    private $filterRepository;
    private $questionnaireRepository;
    private $partRepository;
    private $ruleRepository;

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
            new FilterValueAfterRegression(),
            new FilterValuesList(),
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
     * Set the filter repository
     * @param \Application\Repository\FilterRepository $filterRepository
     * @return \Application\Service\Calculator\Calculator
     */
    public function setFilterRepository(\Application\Repository\FilterRepository $filterRepository)
    {
        $this->filterRepository = $filterRepository;

        return $this;
    }

    /**
     * Get the filter repository
     * @return \Application\Repository\FilterRepository
     */
    public function getFilterRepository()
    {
        if (!$this->filterRepository) {
            $this->filterRepository = $this->getEntityManager()->getRepository('Application\Model\Filter');
        }

        return $this->filterRepository;
    }

    /**
     * Set the part repository
     * @param \Application\Repository\PartRepository $partRepository
     * @return \Application\Service\Calculator\Calculator
     */
    public function setPartRepository(\Application\Repository\PartRepository $partRepository)
    {
        $this->partRepository = $partRepository;

        return $this;
    }

    /**
     * Get the part repository
     * @return \Application\Repository\PartRepository
     */
    public function getPartRepository()
    {
        if (!$this->partRepository) {
            $this->partRepository = $this->getEntityManager()->getRepository('Application\Model\Part');
        }

        return $this->partRepository;
    }

    /**
     * Set the questionnaire repository
     * @param \Application\Repository\QuestionnaireRepository $questionnaireRepository
     * @return \Application\Service\Calculator\Calculator
     */
    public function setQuestionnaireRepository(\Application\Repository\QuestionnaireRepository $questionnaireRepository)
    {
        $this->questionnaireRepository = $questionnaireRepository;

        return $this;
    }

    /**
     * Get the questionnaire repository
     * @return \Application\Repository\QuestionnaireRepository
     */
    public function getQuestionnaireRepository()
    {
        if (!$this->questionnaireRepository) {
            $this->questionnaireRepository = $this->getEntityManager()->getRepository('Application\Model\Questionnaire');
        }

        return $this->questionnaireRepository;
    }

    /**
     * Set the rule repository
     * @param \Application\Repository\Rule\RuleRepository $ruleRepository
     * @return \Application\Service\Calculator\Calculator
     */
    public function setRuleRepository(\Application\Repository\Rule\RuleRepository $ruleRepository)
    {
        $this->ruleRepository = $ruleRepository;

        return $this;
    }

    /**
     * Get the rule repository
     * @return \Application\Repository\Rule\RuleRepository
     */
    public function getRuleRepository()
    {
        if (!$this->ruleRepository) {
            $this->ruleRepository = $this->getEntityManager()->getRepository('Application\Model\Rule\Rule');
        }

        return $this->ruleRepository;
    }

    /**
     * Returns the name according to 'current' syntax
     * @param string|integer $filterId
     * @return string
     */
    public function getFilterName($filterId)
    {
        static $cache = [];

        if ($filterId == 'current') {
            return $filterId;
        } else {
            if (!array_key_exists($filterId, $cache)) {
                $cache[$filterId] = $this->getFilterRepository()->findOneById($filterId)->getName();
            }

            return $cache[$filterId];
        }
    }

    /**
     * Returns the color according to 'current' syntax
     * @param string|integer $filterId
     * @return string
     */
    public function getFilterColor($filterId)
    {
        static $cache = [];
        if ($filterId == 'current') {
            return null;
        } else {

            if (!array_key_exists($filterId, $cache)) {
                $cache[$filterId] = $this->getFilterRepository()->findOneById($filterId)->getColor();
            }

            return $cache[$filterId];
        }
    }

    /**
     * Returns the name according to 'current' syntax
     * @param string|integer $questionnaireId
     * @return string
     */
    public function getQuestionnaireName($questionnaireId)
    {
        static $cache = [];
        if ($questionnaireId == 'current') {
            return $questionnaireId;
        } else {

            if (!array_key_exists($questionnaireId, $cache)) {
                $cache[$questionnaireId] = $this->getQuestionnaireRepository()->findOneById($questionnaireId)->getName();
            }

            return $cache[$questionnaireId];
        }
    }

    /**
     * Returns the name according to 'current' syntax
     * @param string|integer $partId
     * @return string
     */
    public function getPartName($partId)
    {
        static $cache = [];
        if ($partId == 'current') {
            return $partId;
        } else {

            if (!array_key_exists($partId, $cache)) {
                $cache[$partId] = $this->getPartRepository()->findOneById($partId)->getName();
            }

            return $cache[$partId];
        }
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
     * @param \Application\Service\Calculator\Calculator $calculator
     * @param string $formula GIMS formula
     * @param itneger $currentFilterId
     * @param array $questionnaires
     * @param integer $currentPartId
     * @param integer $year
     * @param array $years
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadyUsedRules
     * @return string
     */
    public function convertRegression(Calculator $calculator, $formula, $currentFilterId, array $questionnaires, $currentPartId, $year, array $years, ArrayCollection $alreadyUsedRules)
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
     * @param string $excelFormula
     * @return null|float
     */
    public function computeExcelFormula($excelFormula)
    {
        $result = \PHPExcel_Calculation::getInstance()->_calculateFormulaValue($excelFormula);

        // In some edge cases, it may happen that we get FALSE or empty double quotes as result,
        // we need to convert it to NULL, otherwise it will be converted to
        // 0 later, which is not correct. Eg: '=IF(FALSE, NULL, NULL)', or '=IF(FALSE,NULL,"")'
        if ($result === false || $result === '""') {
            $result = null;
        }

        return $result;
    }

    /**
     * Returns the formulas structure as an array
     * @param string $formula
     * @return array
     */
    public function getStructure($formula)
    {
        $splitter = '::SPLITTER::';
        $tokenStructures = [];

        // First gather all token structures and replace the token with a reference to its structure
        foreach (array_merge($this->getBasicTokens(), $this->getRegressionTokens()) as $token) {
            $formula = \Application\Utility::pregReplaceUniqueCallback($token->getPattern(), function($matches) use ($token, &$tokenStructures, $splitter) {

                        $key = '::TOKEN_' . count($tokenStructures) . '::';
                        $structure = $token->getStructure($matches, $this);
                        $tokenStructures[$key] = $structure;

                        return $splitter . $key . $splitter;
                    }, $formula);
        }

        // Then split all parts (text/token), and build the final list of structure
        $result = [];
        foreach (explode($splitter, $formula) as $a) {
            if (preg_match('/^::TOKEN_\d+::$/', $a)) {
                $result [] = $tokenStructures[$a];
            } elseif ($a) {
                $result[] = [
                    'type' => 'text',
                    'content' => $a,
                ];
            }
        }

        return $result;
    }

}
