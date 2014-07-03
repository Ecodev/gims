<?php

namespace Application\Validator;

class Rule extends \Zend\Validator\AbstractValidator
{

    const START_EQUAL = 'startEqual';
    const MIXED_TOKENS = 'mixedTokens';
    const BASIC_WITH_REGRESSION = 'basicWithRegression';
    const REGRESSION_WITH_BASIC = 'regressionWithBasic';
    const INVALID_SYNTAX = 'invalidSyntax';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::START_EQUAL => 'Formula must start with an equal sign "=".',
        self::MIXED_TOKENS => 'Both kind of tokens cannot be mixed. Use either only Basic or only Regression tokens.',
        self::BASIC_WITH_REGRESSION => 'Basic tokens cannot be used because the Rule is already in use in Regression context (applied to a filter-country).',
        self::REGRESSION_WITH_BASIC => 'Regression tokens cannot be used because the Rule is already in use in Basic context (applied to a filter-questionnaire, or a questionnaire).',
        self::INVALID_SYNTAX => 'Formula syntax is invalid and cannot be computed.',
    );

    /**
     * @var \Application\Service\Syntax\Parser
     */
    private $parser;

    /**
     * @var boolean
     */
    private $basicTokenUsed = false;

    /**
     * @var boolean
     */
    private $regressionTokenUsed = false;

    /**
     * Constructor
     * @param array|Traversable $options
     */
    public function __construct($options = null)
    {
        parent::__construct($options);
        $this->parser = new \Application\Service\Syntax\Parser();
    }

    /**
     * Returns wether the Rule given is valid
     * @param \Application\Model\Rule\Rule $rule
     */
    public function isValid($rule)
    {
        $this->setValue($rule);

        $basicUsageCount = $rule->getQuestionnaireUsages()->count() + $rule->getFilterQuestionnaireUsages()->count();
        $regressionUsageCount = $rule->getFilterGeonameUsages()->count();
        $convertedFormula = $this->convertDummyValues($rule->getFormula());

        if (!is_string($convertedFormula) || strlen($convertedFormula) === 0 || $convertedFormula[0] != '=') {
            $this->error(self::START_EQUAL);
        }

        if ($this->basicTokenUsed && $regressionUsageCount) {
            $this->error(self::BASIC_WITH_REGRESSION);
        }

        if ($this->regressionTokenUsed && $basicUsageCount) {
            $this->error(self::REGRESSION_WITH_BASIC);
        }

        if ($this->basicTokenUsed && $this->regressionTokenUsed) {
            $this->error(self::MIXED_TOKENS);
        }

        // If we had no errors so far, attempt the final check with PHPExcel
        if (!$this->getMessages()) {
            try {
                $this->parser->computeExcelFormula($convertedFormula);
            } catch (\Exception $exception) {
                $this->error(self::INVALID_SYNTAX);
            }
        }

        return !$this->getMessages();
    }

    /**
     * Convert a GIMS formula into an Excel formula by replacing all tokens by "1" to
     * be able to compute something with PHPExcel
     * @param string $formula
     * @return string
     */
    private function convertDummyValues($formula)
    {
        $this->regressionTokenUsed = false;
        $this->basicTokenUsed = false;

        foreach ($this->parser->getBasicTokens() as $token) {
            $formula = \Application\Utility::pregReplaceUniqueCallback($token->getPattern(), function($matches) use ($token) {

                        if (!$token instanceof \Application\Service\Syntax\BasicSelf && !$token instanceof \Application\Service\Syntax\FilterValueAfterRegression) {
                            $this->basicTokenUsed = true;
                        }

                        return '(1)';
                    }, $formula);
        }

        foreach ($this->parser->getRegressionTokens() as $token) {
            $formula = \Application\Utility::pregReplaceUniqueCallback($token->getPattern(), function($matches) use ($token) {

                        if (!$token instanceof \Application\Service\Syntax\RegressionSelf && !$token instanceof \Application\Service\Syntax\RegressionFilterValue) {
                            $this->regressionTokenUsed = true;
                        }

                        return '(1)';
                    }, $formula);
        }

        return $formula;
    }

}
