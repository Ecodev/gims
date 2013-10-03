<?php

namespace Application\Service\Calculator;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Model\Filter;
use Application\Model\FilterSet;
use Application\Model\Answer;
use Application\Model\Part;
use Application\Model\NumericQuestion;
use Application\Model\Questionnaire;
use Application\Model\Rule\Formula;
use Application\Model\Rule\AbstractFormulaUsage;

/**
 * Common base class for various computation. It includes a local instance cache.
 * That means a single instance of Calculator cannot be used if the data model
 * changes. Once computation was started and data model changes, then you *MUST*
 * create a new instance of Calculator to start from scratch (empty caches).
 */
class Calculator
{

    use \Zend\ServiceManager\ServiceLocatorAwareTrait;

use \Application\Traits\EntityManagerAware;

    private $cacheComputeFilter = array();
    protected $excludedFilters = array();
    private $populationRepository;
    private $questionnaireFormulaRepository;
    private $filterRepository;
    private $questionnaireRepository;
    private $partRepository;
    private $answerRepository;

    /**
     * Set the population repository
     * @param \Application\Repository\PopulationRepository $populationRepository
     * @return \Application\Service\Calculator\Jmp
     */
    public function setPopulationRepository(\Application\Repository\PopulationRepository $populationRepository)
    {
        $this->populationRepository = $populationRepository;

        return $this;
    }

    /**
     * Get the population repository
     * @return \Application\Repository\PopulationRepository
     */
    public function getPopulationRepository()
    {
        if (!$this->populationRepository) {
            $this->populationRepository = $this->getEntityManager()->getRepository('Application\Model\Population');
        }

        return $this->populationRepository;
    }

    /**
     * Set the questionnaireformula repository
     * @param \Application\Repository\questionnaireFormulaRepository $questionnaireFormulaRepository
     * @return \Application\Service\Calculator\Calculator
     */
    public function setQuestionnaireFormulaRepository(\Application\Repository\questionnaireFormulaRepository $questionnaireFormulaRepository)
    {
        $this->questionnaireFormulaRepository = $questionnaireFormulaRepository;

        return $this;
    }

    /**
     * Get the questionnaireformula repository
     * @return \Application\Repository\questionnaireFormulaRepository
     */
    public function getQuestionnaireFormulaRepository()
    {
        if (!$this->questionnaireFormulaRepository) {
            $this->questionnaireFormulaRepository = $this->getEntityManager()->getRepository('Application\Model\Rule\QuestionnaireFormula');
        }

        return $this->questionnaireFormulaRepository;
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
     * Set the answer repository
     * @param \Application\Repository\AnswerRepository $answerRepository
     * @return \Application\Service\Calculator\Calculator
     */
    public function setAnswerRepository(\Application\Repository\AnswerRepository $answerRepository)
    {
        $this->answerRepository = $answerRepository;

        return $this;
    }

    /**
     * Get the answer repository
     * @return \Application\Repository\AnswerRepository
     */
    public function getAnswerRepository()
    {
        if (!$this->answerRepository) {
            $this->answerRepository = $this->getEntityManager()->getRepository('Application\Model\Answer');
        }

        return $this->answerRepository;
    }

    /**
     * Returns a unique identifying all arguments, so we can use the result as cache key
     * @param array $args
     * @return string
     */
    protected function getCacheKey(array $args)
    {
        $key = '';
        foreach ($args as $arg) {
            if (is_null($arg))
                $key .= '[[NULL]]';
            else if (is_object($arg))
                $key .= spl_object_hash($arg);
            else if (is_array($arg))
                $key .= $this->getCacheKey($arg);
            else
                $key .= $arg;
        }

        return $key;
    }

    /**
     * Returns the computed value of the given filter, based on the questionnaire's available answers
     * @param \Application\Model\Filter $filter
     * @param \Application\Model\Questionnaire $questionnaire
     * @param \Application\Model\Part $part
     * @return float|null null if no answer at all, otherwise the percentage value
     */
    public function computeFilter(Filter $filter, Questionnaire $questionnaire, Part $part, ArrayCollection $alreadyUsedFormulas = null)
    {
        $key = $this->getCacheKey(func_get_args());
        if (array_key_exists($key, $this->cacheComputeFilter)) {
            return $this->cacheComputeFilter[$key];
        }
        if (!$alreadyUsedFormulas)
            $alreadyUsedFormulas = new ArrayCollection();

        $result = $this->computeFilterInternal($filter, $questionnaire, $part, $alreadyUsedFormulas, new ArrayCollection());

        $this->cacheComputeFilter[$key] = $result;

        return $result;
    }

    /**
     * Returns the computed value of the given filter, based on the questionnaire's available answers
     * @param \Application\Model\Filter $filter
     * @param \Application\Model\Questionnaire $questionnaire
     * @param \Application\Model\Part $part
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadyUsedFormulas already used formula to be exclude when computing
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadySummedFilters will be used to avoid duplicates
     * @return float|null null if no answer at all, otherwise the percentage value
     */
    private function computeFilterInternal(Filter $filter, Questionnaire $questionnaire, Part $part, ArrayCollection $alreadyUsedFormulas, ArrayCollection $alreadySummedFilters)
    {
        // @todo for sylvain: the logic goes as follows: if the filter id is contained within excludeFilters, skip calculation.
        if (in_array($filter->getId(), $this->excludedFilters)) {
            return null;
        }
        // Avoid duplicates
        if ($alreadySummedFilters->contains($filter)) {
            return null;
        } else {
            $alreadySummedFilters->add($filter);
        }

        // If the filter have a specified answer, returns it (skip all computation)
        $answerValue = $this->getAnswerRepository()->getValuePercent($questionnaire, $filter, $part);
        if (!is_null($answerValue)) {
            return $answerValue;
        }

        // If the filter has a formula, returns its value
        foreach ($filter->getFilterRules() as $filterRule) {
            if ($filterRule->getFormula() && $filterRule->getQuestionnaire() === $questionnaire && $filterRule->getPart() === $part && !$alreadyUsedFormulas->contains($filterRule)) {
                return $this->computeFormula($filterRule, $alreadyUsedFormulas);
            }
        }

        // Summer to sum values of given filters, but only if non-null (to preserve null value if no answer at all)
        $summer = function(\IteratorAggregate $filters) use ($questionnaire, $part, $alreadySummedFilters, $alreadyUsedFormulas) {
                    $sum = null;
                    foreach ($filters as $f) {
                        $summandValue = $this->computeFilterInternal($f, $questionnaire, $part, $alreadyUsedFormulas, $alreadySummedFilters);
                        if (!is_null($summandValue)) {
                            $sum += $summandValue;
                        }
                    }

                    return $sum;
                };

        // First, attempt to sum summands
        $sum = $summer($filter->getSummands());

        // If no sum so far, we use children instead. This is "normal case"
        if (is_null($sum)) {
            $sum = $summer($filter->getChildren());
        }

        return $sum;
    }

    /**
     * Compute the value of a formula based on GIMS syntax.
     * For details about syntax, @see \Application\Model\Rule\Formula
     * @param \Application\Model\Rule\AbstractFormulaUsage $usage
     * @return mixed
     * @throws \Exception
     */
    public function computeFormula(AbstractFormulaUsage $usage, ArrayCollection $alreadyUsedFormulas = null)
    {
        if (!$alreadyUsedFormulas) {
            $alreadyUsedFormulas = new ArrayCollection();
        }

        $alreadyUsedFormulas->add($usage);
        $formula = $usage->getFormula();
        $currentQuestionnaire = $usage->getQuestionnaire();
        $currentPart = $usage->getPart();
        $currentFilter = $usage->getFilter();

        $originalFormula = $formula->getFormula();

        // Replace {F#12,Q#34,P#56} with Filter value
        $convertedFormulas = preg_replace_callback('/\{F#(\d+|current),Q#(\d+|current),P#(\d+|current)\}/', function($matches) use ($currentFilter, $currentQuestionnaire, $currentPart, $alreadyUsedFormulas) {
                    $filterId = $matches[1];
                    $questionnaireId = $matches[2];
                    $partId = $matches[3];

                    $filter = $filterId == 'current' ? $currentFilter : $this->getFilterRepository()->findOneById($filterId);
                    $questionnaire = $questionnaireId == 'current' ? $currentQuestionnaire : $this->getQuestionnaireRepository()->findOneById($questionnaireId);
                    $part = $partId == 'current' ? $currentPart : $this->getPartRepository()->findOneById($partId);

                    $value = $this->computeFilter($filter, $questionnaire, $part, $alreadyUsedFormulas);

                    return is_null($value) ? 'NULL' : $value;
                }, $originalFormula);

        // Replace {F#12,Q#34} with Unofficial Filter name, or NULL if no Unofficial Filter
        $convertedFormulas = preg_replace_callback('/\{F#(\d+),Q#(\d+|current)\}/', function($matches) use ($currentQuestionnaire) {
                    $officialFilterId = $matches[1];
                    $questionnaireId = $matches[2];

                    $questionnaire = $questionnaireId == 'current' ? $currentQuestionnaire->getId() : $questionnaireId;

                    $unofficialFilter = $this->getFilterRepository()->findOneBy(array(
                        'officialFilter' => $officialFilterId,
                        'questionnaire' => $questionnaire,
                    ));

                    if ($unofficialFilter) {
                        // Format string for Excel formula
                        return '"' . str_replace('"', '""', $unofficialFilter->getName()) . '"';
                    } else {
                        return 'NULL';
                    }
                }, $convertedFormulas);

        // Replace {Fo#12,Q#34,P#56} with QuestionnaireFormula value
        $convertedFormulas = preg_replace_callback('/\{Fo#(\d+),Q#(\d+|current),P#(\d+|current)\}/', function($matches) use ($currentFilter, $currentQuestionnaire, $currentPart, $formula, $originalFormula, $alreadyUsedFormulas) {
                    $formulaId = $matches[1];
                    $questionnaireId = $matches[2];
                    $partId = $matches[3];

                    $questionnaire = $questionnaireId == 'current' ? $currentQuestionnaire->getId() : $questionnaireId;
                    $part = $partId == 'current' ? $currentPart->getId() : $partId;

                    $criteria = array(
                        'formula' => $formulaId,
                        'questionnaire' => $questionnaire,
                        'part' => $part,
                    );
                    $questionnaireFormula = $this->getQuestionnaireFormulaRepository()->findOneBy($criteria);

                    if (!$questionnaireFormula) {
                        throw new \Exception('Reference to non existing QuestionnaireFormula ' . $matches[0] . ' with ' . var_export($criteria, true) . ' in  Formula#' . $formula->getId() . ', "' . $formula->getName() . '": ' . $originalFormula);
                    }

                    $value = $this->computeFormula($questionnaireFormula, $alreadyUsedFormulas);

                    return is_null($value) ? 'NULL' : $value;
                }, $convertedFormulas);

        // Replace {Q#34,P#56} with population data
        $convertedFormulas = preg_replace_callback('/\{Q#(\d+|current),P#(\d+|current)\}/', function($matches) use ($currentQuestionnaire, $currentPart) {
                    $questionnaireId = $matches[1];
                    $partId = $matches[2];

                    $questionnaire = $this->getQuestionnaireRepository()->findOneById($questionnaireId == 'current' ? $currentQuestionnaire : $questionnaireId);
                    $part = $this->getPartRepository()->findOneById($partId == 'current' ? $currentPart : $partId);

                    return $this->getPopulationRepository()->getOneByQuestionnaire($questionnaire, $part)->getPopulation();
                }, $convertedFormulas);

        // Replace {self} with computed value without this formula
        $convertedFormulas = preg_replace_callback('/\{self\}/', function() use ($currentFilter, $currentQuestionnaire, $currentPart, $alreadyUsedFormulas) {

                    $value = $this->computeFilter($currentFilter, $currentQuestionnaire, $currentPart, $alreadyUsedFormulas);

                    return is_null($value) ? 'NULL' : $value;
                }, $convertedFormulas);

        $result = \PHPExcel_Calculation::getInstance()->_calculateFormulaValue($convertedFormulas);

        // In some edge cases, it may happen that we get FALSE or empty double quotes as result,
        // we need to convert it to NULL, otherwise it will be converted to
        // 0 later, which is not correct. Eg: '=IF(FALSE, NULL, NULL)', or '=IF(FALSE,NULL,"")'
        if ($result === false || $result === '""') {
            $result = null;
        }

        return $result;
    }

}
