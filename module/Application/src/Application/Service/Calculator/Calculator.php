<?php

namespace Application\Service\Calculator;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Model\Filter;
use Application\Model\Part;
use Application\Model\Questionnaire;
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
    private $filterRuleRepository;

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
     * @param \Application\Repository\Rule\QuestionnaireFormulaRepository $questionnaireFormulaRepository
     * @return \Application\Service\Calculator\Calculator
     */
    public function setQuestionnaireFormulaRepository(\Application\Repository\Rule\QuestionnaireFormulaRepository $questionnaireFormulaRepository)
    {
        $this->questionnaireFormulaRepository = $questionnaireFormulaRepository;

        return $this;
    }

    /**
     * Get the questionnaireformula repository
     * @return \Application\Repository\Rule\QuestionnaireFormulaRepository
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
     * Set the filterRule repository
     * @param \Application\Repository\Rule\FilterRuleRepository $filterRuleRepository
     * @return \Application\Service\Calculator\Calculator
     */
    public function setFilterRuleRepository(\Application\Repository\Rule\FilterRuleRepository $filterRuleRepository)
    {
        $this->filterRuleRepository = $filterRuleRepository;

        return $this;
    }

    /**
     * Get the filterRule repository
     * @return \Application\Repository\Rule\FilterRuleRepository
     */
    public function getFilterRuleRepository()
    {
        if (!$this->filterRuleRepository) {
            $this->filterRuleRepository = $this->getEntityManager()->getRepository('Application\Model\Rule\FilterRule');
        }

        return $this->filterRuleRepository;
    }

    /**
     * Returns the computed value of the given filter, based on the questionnaire's available answers
     * @param integer $filterId
     * @param integer $questionnaireId
     * @param integer $partId
     * @return float|null null if no answer at all, otherwise the percentage value
     */
    public function computeFilter($filterId, $questionnaireId, $partId, ArrayCollection $alreadyUsedFormulas = null)
    {
        _log()->debug(__FUNCTION__, array('start', $filterId));
        $key = \Application\Utility::getCacheKey(func_get_args());
        if (array_key_exists($key, $this->cacheComputeFilter)) {
            return $this->cacheComputeFilter[$key];
        }

        if (!$alreadyUsedFormulas)
            $alreadyUsedFormulas = new ArrayCollection();

        $result = $this->computeFilterInternal($filterId, $questionnaireId, $partId, $alreadyUsedFormulas, new ArrayCollection());

        $this->cacheComputeFilter[$key] = $result;
        _log()->debug(__FUNCTION__, array('end', $filterId, $result));
        return $result;
    }

    /**
     * Returns the computed value of the given filter, based on the questionnaire's available answers
     * @param integer $filterId
     * @param integer $questionnaireId
     * @param integer $partId
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadyUsedFormulas already used formula to be exclude when computing
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadySummedFilters will be used to avoid duplicates
     * @return float|null null if no answer at all, otherwise the percentage value
     */
    private function computeFilterInternal($filterId, $questionnaireId, $partId, ArrayCollection $alreadyUsedFormulas, ArrayCollection $alreadySummedFilters)
    {

        _log()->debug(__FUNCTION__, array($filterId, $questionnaireId, $partId));
        // @todo for sylvain: the logic goes as follows: if the filter id is contained within excludeFilters, skip calculation.
        if (in_array($filterId, $this->excludedFilters)) {
            return null;
        }

        // Avoid duplicates
        if ($alreadySummedFilters->contains($filterId)) {
            return null;
        } else {
            $alreadySummedFilters->add($filterId);
        }

        // If the filter have a specified answer, returns it (skip all computation)
        $answerValue = $this->getAnswerRepository()->getValuePercent($questionnaireId, $filterId, $partId);
        if (!is_null($answerValue)) {
            return $answerValue;
        }

        // If the filter has a formula, returns its value
        $filterRule = $this->getFilterRuleRepository()->getFirstWithFormula($questionnaireId, $filterId, $partId, $alreadyUsedFormulas);
        if ($filterRule) {
            return $this->computeFormula($filterRule, $alreadyUsedFormulas);
        }

        // First, attempt to sum summands
        $summandIds = $this->getFilterRepository()->getSummandIds($filterId);
        $sum = $this->summer($summandIds, $questionnaireId, $partId, $alreadyUsedFormulas, $alreadySummedFilters);

        // If no sum so far, we use children instead. This is "normal case"
        if (is_null($sum)) {
            $childrenIds = $this->getFilterRepository()->getChildrenIds($filterId);
            $sum = $this->summer($childrenIds, $questionnaireId, $partId, $alreadyUsedFormulas, $alreadySummedFilters);
        }

        return $sum;
    }

    /**
     * Summer to sum values of given filters, but only if non-null (to preserve null value if no answer at all)
     * @param \IteratorAggregate $filterIds
     * @param integer $questionnaireId
     * @param integer $partId
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadyUsedFormulas
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadySummedFilters
     * @return float|null
     */
    private function summer(array $filterIds, $questionnaireId, $partId, ArrayCollection $alreadyUsedFormulas, ArrayCollection $alreadySummedFilters)
    {
        $sum = null;
        foreach ($filterIds as $filterId) {
            $summandValue = $this->computeFilterInternal($filterId, $questionnaireId, $partId, $alreadyUsedFormulas, $alreadySummedFilters);
            if (!is_null($summandValue)) {
                $sum += $summandValue;
            }
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
        $originalFormula = $usage->getFormula()->getFormula();

        _log()->debug(__FUNCTION__, array($usage->getId(), $originalFormula));

        // Replace {F#12,Q#34,P#56} with Filter value
        $convertedFormulas = preg_replace_callback('/\{F#(\d+|current),Q#(\d+|current),P#(\d+|current)\}/', function($matches) use ($usage, $alreadyUsedFormulas) {
                    $filterId = $matches[1];
                    $questionnaireId = $matches[2];
                    $partId = $matches[3];

                    if ($filterId == 'current') {
                        $filterId = $usage->getFilter()->getId();
                    }

                    if ($questionnaireId == 'current') {
                        $questionnaireId = $usage->getQuestionnaire()->getId();
                    }

                    if ($partId == 'current') {
                        $partId = $usage->getPart()->getId();
                    }

                    $value = $this->computeFilter($filterId, $questionnaireId, $partId, $alreadyUsedFormulas);

                    return is_null($value) ? 'NULL' : $value;
                }, $originalFormula);

        // Replace {F#12,Q#34} with Unofficial Filter name, or NULL if no Unofficial Filter
        $convertedFormulas = preg_replace_callback('/\{F#(\d+),Q#(\d+|current)\}/', function($matches) use ($usage) {
                    $officialFilterId = $matches[1];
                    $questionnaireId = $matches[2];

                    if ($questionnaireId == 'current') {
                        $questionnaireId = $usage->getQuestionnaire()->getId();
                    }

                    $unofficialFilterName = $this->getFilterRepository()->getUnofficialName($officialFilterId, $questionnaireId);
                    if (is_null($unofficialFilterName)) {
                        return 'NULL';
                    } else {
                        // Format string for Excel formula
                        return '"' . str_replace('"', '""', $unofficialFilterName) . '"';
                    }
                }, $convertedFormulas);

        // Replace {Fo#12,Q#34,P#56} with QuestionnaireFormula value
        $convertedFormulas = preg_replace_callback('/\{Fo#(\d+),Q#(\d+|current),P#(\d+|current)\}/', function($matches) use ($usage, $alreadyUsedFormulas) {
                    $formulaId = $matches[1];
                    $questionnaireId = $matches[2];
                    $partId = $matches[3];

                    if ($questionnaireId == 'current') {
                        $questionnaireId = $usage->getQuestionnaire()->getId();
                    }

                    if ($partId == 'current') {
                        $partId = $usage->getPart()->getId();
                    }

                    $questionnaireFormula = $this->getQuestionnaireFormulaRepository()->getOneByQuestionnaire($questionnaireId, $partId, $formulaId);

                    if (!$questionnaireFormula) {
                        throw new \Exception('Reference to non existing QuestionnaireFormula ' . $matches[0] . ' in  Formula#' . $usage->getFormula()->getId() . ', "' . $usage->getFormula()->getName() . '": ' . $usage->getFormula()->getFormula());
                    }

                    $value = $this->computeFormula($questionnaireFormula, $alreadyUsedFormulas);

                    return is_null($value) ? 'NULL' : $value;
                }, $convertedFormulas);

        // Replace {Q#34,P#56} with population data
        $convertedFormulas = preg_replace_callback('/\{Q#(\d+|current),P#(\d+|current)\}/', function($matches) use ($usage) {
                    $questionnaireId = $matches[1];
                    $partId = $matches[2];

                    $questionnaire = $questionnaireId == 'current' ? $usage->getQuestionnaire() : $this->getQuestionnaireRepository()->findOneById($questionnaireId);

                    if ($partId == 'current') {
                        $partId = $usage->getPart()->getId();
                    }

                    return $this->getPopulationRepository()->getOneByQuestionnaire($questionnaire, $partId)->getPopulation();
                }, $convertedFormulas);

        // Replace {self} with computed value without this formula
        $convertedFormulas = preg_replace_callback('/\{self\}/', function() use ($usage, $alreadyUsedFormulas) {

                    $value = $this->computeFilter($usage->getFilter()->getId(), $usage->getQuestionnaire()->getId(), $usage->getPart()->getId(), $alreadyUsedFormulas);

                    return is_null($value) ? 'NULL' : $value;
                }, $convertedFormulas);

        $result = \PHPExcel_Calculation::getInstance()->_calculateFormulaValue($convertedFormulas);

        // In some edge cases, it may happen that we get FALSE or empty double quotes as result,
        // we need to convert it to NULL, otherwise it will be converted to
        // 0 later, which is not correct. Eg: '=IF(FALSE, NULL, NULL)', or '=IF(FALSE,NULL,"")'
        if ($result === false || $result === '""') {
            $result = null;
        }

        _log()->debug(__FUNCTION__, array($usage->getId(), $originalFormula, $convertedFormulas, $result));
        return $result;
    }

}
