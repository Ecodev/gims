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
     * @return float|null null if no answer at all, otherwise the value
     */
    public function computeFilter(Filter $filter, Questionnaire $questionnaire, Part $part = null)
    {
        $key = $this->getCacheKey(func_get_args());
        if (array_key_exists($key, $this->cacheComputeFilter)) {
            return $this->cacheComputeFilter[$key];
        }

        $result = $this->computeFilterInternal($filter, $questionnaire, new \Doctrine\Common\Collections\ArrayCollection(), $part);

        $this->cacheComputeFilter[$key] = $result;

        return $result;
    }

    /**
     * Returns the computed value of the given filter, based on the questionnaire's available answers
     * @param \Application\Model\Filter $filter
     * @param \Application\Model\Questionnaire $questionnaire
     * @param \Doctrine\Common\Collections\ArrayCollection $alreadySummedFilters will be used to avoid duplicates
     * @param \Application\Model\Part $part
     * @return float|null null if no answer at all, otherwise the value
     */
    private function computeFilterInternal(Filter $filter, Questionnaire $questionnaire, \Doctrine\Common\Collections\ArrayCollection $alreadySummedFilters, Part $part = null)
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


        $formulaValue = null;
        foreach ($filter->getFilterRules() as $filterRule) {
            $rule = $filterRule->getRule();
            if ($filterRule->getQuestionnaire() == $questionnaire && $filterRule->getPart() == $part) {

                // If filter is defined by a Ratio, returns it
                if ($rule instanceof \Application\Model\Rule\Ratio) {
                    $value = $this->computeFilterInternal($rule->getFilter(), $questionnaire, $alreadySummedFilters, $part);

                    // Preserve null value while multiplying
                    if (!is_null($value))
                        $value = $rule->getRatio() * $value;

                    return $value;
                }
                // If we have a formula, cumulate their value to add them later to normal result
                else if ($rule instanceof \Application\Model\Rule\Formula) {
                    $value = $rule->getValue();
                    if (!is_null($value)) {
                        $formulaValue += $value;
                    }
                }
            }
        }


        // Summer to sum values of given filters, but only if non-null (to preserve null value if no answer at all)
        $summer = function(\IteratorAggregate $filters) use ($questionnaire, $part, $alreadySummedFilters) {
                    $sum = null;
                    foreach ($filters as $f) {
                        $summandValue = $this->computeFilterInternal($f, $questionnaire, $alreadySummedFilters, $part);
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

        // And finally add cumulated formula values (what is called "Estimates" in Excel)
        // TODO: This probably will change once we implement real formula engine
        if (!is_null($formulaValue)) {
            $sum += $formulaValue;
        }

        return $sum;
    }

}
