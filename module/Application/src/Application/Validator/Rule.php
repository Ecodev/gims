<?php

namespace Application\Validator;

class Rule extends \Zend\Validator\AbstractValidator
{

    const START_EQUAL = 'startEqual';
    const MIXED_TOKENS = 'mixedTokens';
    const BEFORE_WITH_AFTER_REGRESSION = 'beforeWithAfterRegression';
    const AFTER_WITH_BEFORE_REGRESSION = 'afterWithBeforeRegression';
    const INVALID_SYNTAX = 'invalidSyntax';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::START_EQUAL => 'Formula must start with an equal sign "=".',
        self::MIXED_TOKENS => 'Both kind of tokens cannot be mixed. Use either only Before Regression or only After Regression tokens.',
        self::BEFORE_WITH_AFTER_REGRESSION => 'Before Regression tokens cannot be used because the Rule is already in use in After Regression context (applied to a filter-country).',
        self::AFTER_WITH_BEFORE_REGRESSION => 'After Regression tokens cannot be used because the Rule is already in use in Before Regression context (applied to a filter-questionnaire, or a questionnaire).',
        self::INVALID_SYNTAX => 'Formula syntax is invalid and cannot be computed.',
    );

    /**
     * @var \Application\Service\Syntax\Parser
     */
    private $parser;

    /**
     * @var boolean
     */
    private $beforeRegressionTokenUsed = false;

    /**
     * @var boolean
     */
    private $afterRegressionTokenUsed = false;

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
     * Returns whether the Rule given is valid
     * @param \Application\Model\Rule\Rule $rule
     */
    public function isValid($rule)
    {
        $this->setValue($rule);

        $beforeRegressionUsageCount = $rule->getQuestionnaireUsages()->count() + $rule->getFilterQuestionnaireUsages()->count();
        $afterRegressionUsageCount = $rule->getFilterGeonameUsages()->count();
        $convertedFormula = $this->convertDummyValues($rule->getFormula());

        if (!is_string($convertedFormula) || strlen($convertedFormula) === 0 || $convertedFormula[0] != '=') {
            $this->error(self::START_EQUAL);
        }

        if ($this->beforeRegressionTokenUsed && $afterRegressionUsageCount) {
            $this->error(self::BEFORE_WITH_AFTER_REGRESSION);
        }

        if ($this->afterRegressionTokenUsed && $beforeRegressionUsageCount) {
            $this->error(self::AFTER_WITH_BEFORE_REGRESSION);
        }

        if ($this->beforeRegressionTokenUsed && $this->afterRegressionTokenUsed) {
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
        $this->afterRegressionTokenUsed = false;
        $this->beforeRegressionTokenUsed = false;

        foreach ($this->parser->getBeforeRegressionTokens() as $token) {
            $formula = \Application\Utility::pregReplaceUniqueCallback($token->getPattern(), function($matches) use ($token) {

                        if (!$token instanceof \Application\Service\Syntax\BothContextInterface) {
                            $this->beforeRegressionTokenUsed = true;
                        }

                        return '(1)';
                    }, $formula);
        }

        foreach ($this->parser->getAfterRegressionTokens() as $token) {
            $formula = \Application\Utility::pregReplaceUniqueCallback($token->getPattern(), function($matches) use ($token) {

                        if (!$token instanceof \Application\Service\Syntax\BothContextInterface) {
                            $this->afterRegressionTokenUsed = true;
                        }

                        return '(1)';
                    }, $formula);
        }

        return $formula;
    }

}
