<?php

namespace Application\Service\Calculator;

use Application\Model\Filter;
use Application\Model\FilterSet;
use Application\Model\Answer;
use Application\Model\Part;
use Application\Model\Question;
use Application\Model\Questionnaire;

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
    private $cacheComputeQuestionnaire = array();

    /**
     * Returns the computed value for the specified filter
     * @param \Application\Model\Filter $filter
     * @param \Application\Model\Questionnaire $questionnaire
     * @param \Application\Model\Part $part
     * @return type
     */
    public function computeFilter(Filter $filter, Questionnaire $questionnaire, Part $part = null)
    {
        $key = spl_object_hash($filter) . spl_object_hash($questionnaire) . ($part ? spl_object_hash($part) : null);

        if (array_key_exists($key, $this->cacheComputeFilter)) {
            return $this->cacheComputeFilter[$key];
        }

        $result = null;
        foreach ($filter->getChildren() as $filter) {
            $computed = $this->computeQuestionnaire($questionnaire, $filter, $part);
            if (!is_null($computed)) {
                $result += $computed;
            }
        }


        $this->cacheComputeFilter[$key] = $result;

        return $result;
    }

    /**
     * Returns the computed value of the given filter, based on the questionnaire's available answers
     * @param \Application\Model\Filter $filter
     * @param \Application\Model\Part $part
     * @return float|null null if no answer at all, otherwise the value
     */
    public function computeQuestionnaire(Questionnaire $questionnaire, Filter $filter, Part $part = null)
    {
        $key = spl_object_hash($questionnaire) . spl_object_hash($filter) . ($part ? spl_object_hash($part) : null);
        if (array_key_exists($key, $this->cacheComputeQuestionnaire)) {
            return $this->cacheComputeQuestionnaire[$key];
        }

        $result = $this->computeQuestionnaireInternal($questionnaire, $filter, new \Doctrine\Common\Collections\ArrayCollection(), $part);

        $this->cacheComputeQuestionnaire[$key] = $result;

        return $result;
    }

    /**
     * Returns the computed value of the given filter, based on the questionnaire's available answers
     * @param \Application\Model\Filter $filter
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadySummedFilters will be used to avoid duplicates
     * @param \Application\Model\Part $part
     * @return float|null null if no answer at all, otherwise the value
     */
    private function computeQuestionnaireInternal(Questionnaire $questionnaire, Filter $filter, \Doctrine\Common\Collections\ArrayCollection $alreadySummedFilters, Part $part = null)
    {
        // Avoid duplicates
        if ($alreadySummedFilters->contains($filter)) {
            return null;
        } else {
            $alreadySummedFilters->add($filter);
        }

        // If the filter have a specified answer, returns it (skip all computation)
        foreach ($questionnaire->getAnswers() as $answer) {
            $answerFilter = $answer->getQuestion()->getFilter()->getOfficialFilter() ? : $answer->getQuestion()->getFilter();
            if ($answerFilter === $filter && $answer->getPart() == $part) {

                $alreadySummedFilters->add(true);
                return $answer->getValueAbsolute();
            }
        }


        // Summer to sum values of given filters, but only if non-null (to preserve null value if no answer at all)
        $summer = function(\IteratorAggregate $filters) use ($questionnaire, $part, $alreadySummedFilters) {
                    $sum = null;
                    foreach ($filters as $f) {
                        $summandValue = $this->computeQuestionnaireInternal($questionnaire, $f, $alreadySummedFilters, $part);
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

}
