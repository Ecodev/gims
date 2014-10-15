<?php

namespace Application\Service\Calculator;

use Doctrine\Common\Collections\ArrayCollection;
use Application\Model\Rule\AbstractQuestionnaireUsage;

/**
 * Common base class for various computation. It includes a local instance cache.
 * That means a single instance of AbstractCalculator cannot be used if the data model
 * changes. Once computation was started and data model changes, then you *MUST*
 * create a new instance to start from scratch (empty caches).
 */
abstract class AbstractCalculator
{

    use \Zend\ServiceManager\ServiceLocatorAwareTrait;

use \Application\Traits\EntityManagerAware;

    private $cache;
    private $cacheComputeFilter = array();
    protected $overriddenFilters = array();
    private $populationRepository;
    private $questionnaireUsageRepository;
    private $filterRepository;
    private $questionnaireRepository;
    private $partRepository;
    private $answerRepository;
    private $filterQuestionnaireUsageRepository;
    private $parser;

    /**
     * Get the cache instance
     * @return \Application\Service\Calculator\Cache\CacheInterface
     */
    public function getCache()
    {
        if (!$this->cache) {
            $this->cache = $this->getServiceLocator()->get('Cache\Computing');
        }

        return $this->cache;
    }

    /**
     * Set the population repository
     * @param \Application\Repository\PopulationRepository $populationRepository
     * @return self
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
     * Set the questionnaireusage repository
     * @param \Application\Repository\Rule\QuestionnaireUsageRepository $questionnaireUsageRepository
     * @return self
     */
    public function setQuestionnaireUsageRepository(\Application\Repository\Rule\QuestionnaireUsageRepository $questionnaireUsageRepository)
    {
        $this->questionnaireUsageRepository = $questionnaireUsageRepository;

        return $this;
    }

    /**
     * Get the questionnaireusage repository
     * @return \Application\Repository\Rule\QuestionnaireUsageRepository
     */
    public function getQuestionnaireUsageRepository()
    {
        if (!$this->questionnaireUsageRepository) {
            $this->questionnaireUsageRepository = $this->getEntityManager()->getRepository('Application\Model\Rule\QuestionnaireUsage');
        }

        return $this->questionnaireUsageRepository;
    }

    /**
     * Set the filter repository
     * @param \Application\Repository\FilterRepository $filterRepository
     * @return self
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
     * @return self
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
     * @return self
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
     * @return self
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
     * Set the filterQuestionnaireUsage repository
     * @param \Application\Repository\Rule\FilterQuestionnaireUsageRepository $filterQuestionnaireUsageRepository
     * @return self
     */
    public function setFilterQuestionnaireUsageRepository(\Application\Repository\Rule\FilterQuestionnaireUsageRepository $filterQuestionnaireUsageRepository)
    {
        $this->filterQuestionnaireUsageRepository = $filterQuestionnaireUsageRepository;

        return $this;
    }

    /**
     * Get the filterQuestionnaireUsage repository
     * @return \Application\Repository\Rule\FilterQuestionnaireUsageRepository
     */
    public function getFilterQuestionnaireUsageRepository()
    {
        if (!$this->filterQuestionnaireUsageRepository) {
            $this->filterQuestionnaireUsageRepository = $this->getEntityManager()->getRepository('Application\Model\Rule\FilterQuestionnaireUsage');
        }

        return $this->filterQuestionnaireUsageRepository;
    }

    /**
     * Get the syntax parser
     * @return \Application\Service\Syntax\Parser
     */
    public function getParser()
    {
        if (!$this->parser) {
            $this->parser = new \Application\Service\Syntax\Parser();
        }

        return $this->parser;
    }

    /**
     * Get the overridden filter
     * @return array
     */
    public function getOverriddenFilters()
    {
        return $this->overriddenFilters;
    }

    /**
     * Set the filter which must be overridden with given value
     * @param array $overriddenFilters [questionnaireId => [filterId => [partId => value]]]
     * @return self
     */
    public function setOverriddenFilters(array $overriddenFilters)
    {
        $this->overriddenFilters = $overriddenFilters;

        return $this;
    }

    /**
     * Returns the computed value of the given filter, based on the questionnaire's available answers
     * @param integer $filterId
     * @param integer $questionnaireId
     * @param integer $partId
     * @param boolean $useSecondStepRules
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadyUsedFormulas
     * @return float|null null if no answer at all, otherwise the percentage value
     */
    public function computeFilter($filterId, $questionnaireId, $partId, $useSecondStepRules = false, ArrayCollection $alreadyUsedFormulas = null)
    {
        $key = \Application\Utility::getPersistentCacheKey([$filterId, $questionnaireId, $partId, $useSecondStepRules, $this->overriddenFilters]);
        if (array_key_exists($key, $this->cacheComputeFilter)) {
            return $this->cacheComputeFilter[$key];
        }

        if (!$alreadyUsedFormulas) {
            $alreadyUsedFormulas = new ArrayCollection();
        }

        $result = $this->computeFilterInternal($filterId, $questionnaireId, $partId, $useSecondStepRules, $alreadyUsedFormulas, new ArrayCollection());

        $this->cacheComputeFilter[$key] = $result;

        return $result;
    }

    /**
     * Returns the computed value of the given filter, based on the questionnaire's available answers
     * @param integer $filterId
     * @param integer $questionnaireId
     * @param integer $partId
     * @param boolean $useSecondStepRules
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadyUsedFormulas already used formula to be exclude when computing
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadySummedFilters will be used to avoid duplicates
     * @return float|null null if no answer at all, otherwise the percentage value
     */
    private function computeFilterInternal($filterId, $questionnaireId, $partId, $useSecondStepRules, ArrayCollection $alreadyUsedFormulas, ArrayCollection $alreadySummedFilters)
    {
        // Avoid duplicates
        if ($alreadySummedFilters->contains($filterId)) {
            return null;
        } else {
            $alreadySummedFilters->add($filterId);
            $this->getCache()->record("F#$filterId,Q#$questionnaireId,P#$partId"); // The cell value is used
            $this->getCache()->record("filter:$filterId"); // and also the filter is globally used (so we can invalid if summands change)
            $this->getCache()->record("questionnaire:$questionnaireId"); // and also the questionnaire is globally used (so we can invalid if questions or questionnaire's geoname change)
        }

        // If the questionnaire has some overriding values
        // use array_key_exists() function cause overridden value may contain null that return false with isset()
        if (array_key_exists($questionnaireId, $this->overriddenFilters)) {

            if (!is_array($this->overriddenFilters[$questionnaireId])) {
                return $this->overriddenFilters[$questionnaireId];
            }

            // If the specific filter has an overriding value, use the override
            if (isset($this->overriddenFilters[$questionnaireId][$filterId]) && array_key_exists($partId, $this->overriddenFilters[$questionnaireId][$filterId])) {
                return $this->overriddenFilters[$questionnaireId][$filterId][$partId];
            }
        }

        // If we use second step rules, we let them override answers
        // That means the end-user can enter an answer, but still choose to ignore it (via exclude rule)
        if ($useSecondStepRules) {
            $filterQuestionnaireUsage = $this->getFilterQuestionnaireUsageRepository()->getFirst($questionnaireId, $filterId, $partId, $useSecondStepRules, $alreadyUsedFormulas);
            if ($filterQuestionnaireUsage) {
                return $this->computeFormulaBeforeRegression($filterQuestionnaireUsage, $alreadyUsedFormulas, $useSecondStepRules);
            }
        }

        // If the filter have a specified answer, returns it (skip all computation)
        $answerValue = $this->getAnswerRepository()->getValue($questionnaireId, $filterId, $partId);

        if (!is_null($answerValue)) {
            return $answerValue;
        }

        // If the filter has a formula, returns its value
        $filterQuestionnaireUsage = $this->getFilterQuestionnaireUsageRepository()->getFirst($questionnaireId, $filterId, $partId, false, $alreadyUsedFormulas);
        if ($filterQuestionnaireUsage) {
            return $this->computeFormulaBeforeRegression($filterQuestionnaireUsage, $alreadyUsedFormulas, $useSecondStepRules);
        }

        // First, attempt to sum summands
        $summandIds = $this->getFilterRepository()->getSummandIds($filterId);
        $sum = $this->summer($summandIds, $questionnaireId, $partId, $useSecondStepRules, $alreadyUsedFormulas, $alreadySummedFilters);

        // If no sum so far, we use children instead. This is "normal case"
        if (is_null($sum)) {
            $childrenIds = $this->getFilterRepository()->getChildrenIds($filterId);
            $sum = $this->summer($childrenIds, $questionnaireId, $partId, $useSecondStepRules, $alreadyUsedFormulas, $alreadySummedFilters);
        }

        return $sum;
    }

    /**
     * Summer to sum values of given filters, but only if non-null (to preserve null value if no answer at all)
     * @param array|\IteratorAggregate $filterIds
     * @param integer $questionnaireId
     * @param integer $partId
     * @param boolean $useSecondStepRules
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadyUsedFormulas
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadySummedFilters
     * @return float|null
     */
    private function summer(array $filterIds, $questionnaireId, $partId, $useSecondStepRules, ArrayCollection $alreadyUsedFormulas, ArrayCollection $alreadySummedFilters)
    {
        $sum = null;
        foreach ($filterIds as $filterId) {
            $summandValue = $this->computeFilterInternal($filterId, $questionnaireId, $partId, $useSecondStepRules, $alreadyUsedFormulas, $alreadySummedFilters);
            if (!is_null($summandValue)) {
                $sum += $summandValue;
            }
        }

        return $sum;
    }

    /**
     * Compute the value of a formula based on GIMS syntax.
     * For details about syntax, @see \Application\Model\Rule\Rule
     * @param \Application\Model\Rule\AbstractQuestionnaireUsage $usage
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadyUsedFormulas
     * @param boolean $useSecondStepRules
     * @return null|float
     */
    public function computeFormulaBeforeRegression(AbstractQuestionnaireUsage $usage, ArrayCollection $alreadyUsedFormulas = null, $useSecondStepRules = false)
    {
        if (!$alreadyUsedFormulas) {
            $alreadyUsedFormulas = new ArrayCollection();
        }
        $alreadyUsedFormulas->add($usage);
        $this->getCache()->record('rule:' . $usage->getRule()->getId());
        $this->getCache()->record($usage->getCacheKey());

        $originalFormula = $usage->getRule()->getFormula();
        $convertedFormula = $this->getParser()->convertBeforeRegression($this, $originalFormula, $usage, $alreadyUsedFormulas, $useSecondStepRules);
        $result = $this->getParser()->computeExcelFormula($convertedFormula);

        _log()->debug(__METHOD__, array($usage->getId(), $usage->getRule()->getName(), $originalFormula, $convertedFormula, $result));

        return $result;
    }

}
