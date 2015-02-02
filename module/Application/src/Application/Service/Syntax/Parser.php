<?php

namespace Application\Service\Syntax;

use Application\Model\Rule\AbstractQuestionnaireUsage;
use Application\Service\Calculator\Calculator;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Parser used to convert GIMS formula syntax into pure Excel formula syntax.
 */
class Parser
{

    use \Zend\ServiceManager\ServiceLocatorAwareTrait;

    use \Application\Traits\EntityManagerAware;

    /**
     * Use pre-defined, very visible, and very distinct
     * colors to be used as highlight
     * @var array
     */
    private $availableColors = [
        'blue',
        'red',
        'green',
        'purple',
        'orange',
        'magenta',
        'lime',
        'cyan',
        'yellow',
        'teal',
        'maroon',
        'navy',
        'olive',
        'tomato',
    ];
    private $usedColors = [];
    private $filterRepository;
    private $questionnaireRepository;
    private $partRepository;
    private $ruleRepository;

    /**
     * @var array
     */
    private $beforeRegressions;

    /**
     * @var array
     */
    private $afterRegressions;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->beforeRegressions = [
            new BeforeRegression\FilterValue(),
            new BeforeRegression\QuestionName(),
            new BeforeRegression\QuestionnaireUsageValue(),
            new BeforeRegression\PopulationValue(),
            new BeforeRegression\SelfToken(),
            new BeforeRegression\FilterValueAfterRegression(),
            new BeforeRegression\FilterValuesList(),
        ];

        $this->afterRegressions = [
            new AfterRegression\FilterValuesList(),
            new AfterRegression\FilterValue(),
            new AfterRegression\CumulatedPopulation(),
            new AfterRegression\SelfToken(),
            new AfterRegression\Year(),
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
     * Get an array of tokens to be used before regression
     * @return BeforeRegression\AbstractToken[]
     */
    public function getBeforeRegressionTokens()
    {
        return $this->beforeRegressions;
    }

    /**
     * Get an array of tokens to be used after regression
     * @return AfterRegression\AbstractToken[]
     */
    public function getAfterRegressionTokens()
    {
        return $this->afterRegressions;
    }

    /**
     * Convert the given GIMS formula into an Excel formula by using before regression tokens
     * @param \Application\Service\Calculator\Calculator $calculator
     * @param string $formula GIMS formula
     * @param \Application\Model\Rule\AbstractQuestionnaireUsage $usage
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadyUsedFormulas
     * @param boolean $useSecondStepRules
     * @return string
     */
    public function convertBeforeRegression(Calculator $calculator, $formula, AbstractQuestionnaireUsage $usage, ArrayCollection $alreadyUsedFormulas, $useSecondStepRules)
    {
        foreach ($this->getBeforeRegressionTokens() as $token) {
            $formula = \Application\Utility::pregReplaceUniqueCallback($token->getPattern(), function ($matches) use ($token, $calculator, $usage, $alreadyUsedFormulas, $useSecondStepRules) {
                        return $token->replace($calculator, $matches, $usage, $alreadyUsedFormulas, $useSecondStepRules);
                    }, $formula);
        }

        return $formula;
    }

    /**
     * Convert the given GIMS formula into an Excel formula by using after regression tokens
     * @param \Application\Service\Calculator\Calculator $calculator
     * @param string $formula GIMS formula
     * @param itneger $currentFilterId
     * @param array $questionnaires
     * @param integer $currentPartId
     * @param integer $year
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadyUsedRules
     * @return string
     */
    public function convertAfterRegression(Calculator $calculator, $formula, $currentFilterId, array $questionnaires, $currentPartId, $year, ArrayCollection $alreadyUsedRules)
    {
        foreach ($this->getAfterRegressionTokens() as $token) {
            $formula = \Application\Utility::pregReplaceUniqueCallback($token->getPattern(), function ($matches) use ($token, $calculator, $currentFilterId, $questionnaires, $currentPartId, $year, $alreadyUsedRules) {
                        return $token->replace($calculator, $matches, $currentFilterId, $questionnaires, $currentPartId, $year, $alreadyUsedRules);
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
        $this->resetHighlightColors();
        $splitter = '::SPLITTER::';
        $tokenStructures = [];

        // First gather all token structures and replace the token with a reference to its structure
        foreach (array_merge($this->getBeforeRegressionTokens(), $this->getAfterRegressionTokens()) as $token) {
            $formula = \Application\Utility::pregReplaceUniqueCallback($token->getPattern(), function ($matches) use ($token, &$tokenStructures, $splitter) {

                        $key = '::TOKEN_' . count($tokenStructures) . '::';
                        $structure = $token->getStructure($matches, $this);

                        // Inject highlight color if needed
                        if ($token instanceof NeedHighlightColorInterface) {
                            $structure['highlightColor'] = $this->getHighlightColor($structure);
                        }

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

    /**
     * Returns one of the pre-defined colors. Will always be same result for same structure
     * @param array $structure
     * @return string CSS color value
     */
    private function getHighlightColor(array $structure)
    {
        $colorKey = serialize($structure);
        if (array_key_exists($colorKey, $this->usedColors)) {
            $highlightColor = $this->usedColors[$colorKey];
        } else {
            $colorIndex = count($this->usedColors) % count($this->availableColors);
            $highlightColor = $this->availableColors[$colorIndex];
            $this->usedColors[$colorKey] = $highlightColor;
        }

        return $highlightColor;
    }

    /**
     * Reset used highlight colors
     */
    private function resetHighlightColors()
    {
        $this->usedColors = [];
    }
}
